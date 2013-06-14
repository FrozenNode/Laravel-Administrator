<?php
namespace Frozennode\Administrator;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use \DateTime;
use Illuminate\Support\Facades\DB;
use \Exception;
use Frozennode\Administrator\Fields\Field;

class ModelHelper {


	/**
	 * Gets a model given an id
	 *
	 * @param id		$id
	 * @param bool		$updateRelationships	//if true, the model will come back with an extra "[field]_options" attribute for relationships
	 * @param bool		$includeAllColumns		//if true, all columns will be included (only use for non-saving items)
	 * @param bool		$saving					//if true, don't include the admin_item_link
	 *
	 * @return object|null	$model
	 * object with data => if the id exists
	 * new object => if id doesn't exist
	 * null => if there is no model by that name
	 */
	public static function getModel($id = 0, $updateRelationships = false, $includeAllColumns = false, $saving = false)
	{
		//if we're getting an existing model, we'll want to first get the edit fields without the relationships loaded
		$config = App::make('itemconfig');
		$model = $config->model;
		$editFields = Field::getEditFields($config, ($id ? false : true));

		//make sure the edit fields are included
		foreach ($editFields['objectFields'] as $field => $obj)
		{
			if (!$obj->relationship && !array_key_exists($field, $config->columns['includedColumns']))
			{
				$config->columns['includedColumns'][$field] = $model->getTable().'.'.$field;
			}
		}

		//get the model
		if ($includeAllColumns)
		{
			$model = $model::find($id);
		}
		else
		{
			$model = $model::find($id, $config->columns['includedColumns']);
		}

		$model = $model ? $model : $config->model;

		//now we get the edit fields with the relationships loaded
		$editFields = Field::getEditFields($config);

		//if the model exists, load up the existing related items
		if ($model->exists && !$saving)
		{
			//make sure the relationships are loaded
			foreach ($editFields['objectFields'] as $field => $info)
			{
				if ($info->relationship)
				{
					//if this is a hmabt, we want to sort our initial values
					if ($info->multipleValues)
					{
						//if a sort_field is provided, use it, otherwise sort by the name field
						if ($info->sortField)
						{
							$relatedItems = $model->{$field}()->orderBy($info->sortField)->get();
						}
						else
						{
							$relatedItems = $model->{$field}()->orderBy($info->nameField)->get();
						}
					}
					else
					{
						$relatedItems = $model->{$field}()->get();
					}

					//get all existing values for this relationship
					if ($relatedItems)
					{
						//the array that holds all the ids of the currently-related items
						$relationsArray = array();

						//the id-indexed array that holds all of the select option data for a relation.
						//this holds the currently-related items and all of the available options
						$autocompleteArray = array();

						//iterate over the items
						foreach ($relatedItems as $item)
						{
							//if this is a mutliple-value type (i.e. HasMany, HasManyAndBelongsTo), make sure this is an array
							if ($info->multipleValues)
							{
								$relationsArray[] = $item->{$item->getKeyName()};
							}
							else
							{
								$model->setAttribute($field, $item->{$item->getKeyName()});
							}

							//if this is an autocomplete field, we'll need to provide an array of arrays with 'id' and 'text' indexes
							if ($info->autocomplete)
							{
								$autocompleteArray[$item->{$item->getKeyName()}] = array('id' => $item->{$item->getKeyName()}, 'text' => $item->{$info->nameField});
							}
						}

						//if this is a BTM, set the relations array to the property that matches the relationship name
						if ($info->multipleValues)
						{
							$model->{$field} = $relationsArray;
						}

						//set the options attribute if $updateRelationships is true
						if ($updateRelationships)
						{
							$model->setAttribute($field.'_options', $info->options);

							//unset the relationships so we only get back what we need
							$model->relationships = array();
						}

						//set the autocomplete array
						if ($info->autocomplete)
						{
							$model->setAttribute($field.'_autocomplete', $autocompleteArray);
						}
					}
					//if there are no values, then just set an empty array
					else
					{
						$model->{$field} = array();
					}
				}
			}

			//include the item link if one was supplied
			$link = $config->getModelLink($model);

			if (!$saving && $link)
			{
				$model->setAttribute('admin_item_link', $link);
			}
		}

		return $model;
	}

	/**
	 * Gets an instance of the supplied model class
	 *
	 * @param string		$className
	 *
	 * @return null | Eloquent instance
	 */
	public static function getModelInstance($className)
	{
		//check if the class exists at all
		if (class_exists($className))
		{
			$instance = new $className();

			//and if it's an eloquent model
			if (is_a($instance, 'Illuminate\\Database\\Eloquent\\Model'))
			{
				return $instance;
			}
		}

		//otherwise throw an exception
		throw new Exception("Administrator: " . $className  .  trans('administrator::administrator.not_eloquent'));
	}


	/**
	 * Helper that builds a results array (with results and pagination info)
	 *
	 * @param array			$sort (with 'field' and 'direction' keys)
	 * @param array			$filters (see Field::getFilters method for the value types)
	 */
	public static function getRows($sort = null, $filters = null)
	{
		$config = App::make('itemconfig');

		//grab the model instance
		$model = $config->model;

		//update the config sort options
		$config->setSort($sort);
		$sort = $config->sort;

		//get things going by grouping the set
		$query = $model::groupBy($model->getTable().'.'.$model->getKeyName());
		$db_query = $query->getQuery();
		$count_query = DB::table($model->getTable())->groupBy($model->getTable().'.'.$model->getKeyName());

		//set up initial array states for the selects
		$selects = array($model->getTable().'.*');

		//then we set the filters
		if ($filters && is_array($filters))
		{
			foreach ($filters as $filter)
			{
				if (!$fieldObject = Field::get($filter['field'], $filter, $config))
				{
					continue;
				}

				$fieldObject->filterQuery($db_query, $model, $selects);
				$fieldObject->filterQuery($count_query, $model, $selects);
			}
		}

		$db_query->select($selects);

		//determines if the sort should have the table prefixed to it
		$sortOnTable = true;

		//iterate over the columns to check if we need to join any values or add any extra columns
		foreach ($config->columns['columns'] as $field => $column)
		{
			//if this is a related column, we'll need to add some joins
			$column->filterQuery($db_query, $selects, $model);
			$column->filterQuery($count_query, $selects, $model);

			//if this is a related field or
			if ( ($column->isRelated || $column->select) && $column->field === $sort['field'])
			{
				$sortOnTable = false;
			}
		}

		//if the sort is on the model's table, prefix the table name to it
		if ($sortOnTable)
		{
			$sort['field'] = $model->getTable() . '.' . $sort['field'];
		}

		$sql = $query->toSql();

		//then wrap the inner table and perform the count
		$sql = "SELECT COUNT({$model->getKeyName()}) AS aggregate FROM ({$sql}) AS agg";

		//then perform the count query
		$results = $count_query->getConnection()->select($sql, $count_query->getBindings());
		$num_rows = $results[0]->aggregate;
		$page = (int) \Input::get('page', 1);
		$last = (int) ceil($num_rows / $config->rowsPerPage);

		//if the current page is greater than the last page, set the current page to the last page
		$page = $page > $last ? $last : $page;

		//now we need to limit and offset the rows in remembrance of our dear lost friend paginate()
		$query->take($config->rowsPerPage);
		$query->skip($config->rowsPerPage * ($page === 0 ? $page : $page - 1));

		//order the set by the model table's id
		$query->orderBy($sort['field'], $sort['direction']);

		//then retrieve the rows
		$query->getQuery()->select($selects);
		$rows = $query->distinct()->get();
		$results = array();

		//convert the resulting set into arrays
		foreach ($rows as $item)
		{
			//iterate over the included and related columns
			$onTableColumns = array_merge($config->columns['includedColumns'], $config->columns['relatedColumns']);
			$arr = array();

			foreach ($onTableColumns as $field => $col)
			{
				//if this column is in our objects array, render the output with the given value
				if (isset($config->columns['columnObjects'][$field]))
				{
					$arr[$field] = array(
						'raw' => $item->getAttribute($field),
						'rendered' => $config->columns['columnObjects'][$field]->renderOutput($item->getAttribute($field)),
					);
				}
				//otherwise it's likely the primary key column which wasn't included (though it's needed for identification purposes)
				else
				{
					$arr[$field] = array(
						'raw' => $item->getAttribute($field),
						'rendered' => $item->getAttribute($field),
					);
				}
			}
			//then grab the computed, unsortable columns
			foreach ($config->columns['computedColumns'] as $col)
			{
				$arr[$col] = array(
					'raw' => $item->{$col},
					'rendered' => $config->columns['columnObjects'][$col]->renderOutput($item->{$col}),
				);
			}

			$results[] = $arr;
		}

		return array(
			'page' => $page,
			'last' => $last,
			'total' => $num_rows,
			'results' => $results,
		);
	}

	/**
	 * Prepare a model for saving given a post input array
	 *
	 * @param Eloquent		$model
	 *
	 * @return false|object
	 */
	public static function fillModel(&$model)
	{
		$config = App::make('itemconfig');
		$editFields = Field::getEditFields($config);

		//run through the edit fields to see if we need to set relationships
		foreach ($editFields['objectFields'] as $field => $info)
		{
			if (!$info->external)
			{
				$info->fillModel($model, \Input::get($field, NULL));
			}
			//if this is an "external" field (i.e. it's not a column on this model's table), unset it
			else
			{
				$model->__unset($field);
			}
		}
	}

	/**
	 * After a model has been saved, this is called to save the relationships
	 *
	 * @param Eloquent		$model
	 *
	 * @return false|object
	 */
	public static function saveRelationships(&$model)
	{
		$config = App::make('itemconfig');
		$editFields = Field::getEditFields($config);

		//run through the edit fields to see if we need to set relationships
		foreach ($editFields['objectFields'] as $field => $info)
		{
			if ($info->external)
			{
				$info->fillModel($model, \Input::get($field, NULL));
			}
		}
	}

	/**
	 * Given a model, field, type (filter or edit), and constraints (either int or array), returns an array of options
	 *
	 * @param string		$field
	 * @param string		$type			//either 'filter' or 'edit'
	 * @param array			$constraints	//an array of ids of the other model's items
	 * @param array			$selectedItems	//an array of ids that are currently selected
	 * @param string		$term			//the search term
	 *
	 * @return array
	 */
	public static function updateRelationshipOptions($field, $type, $constraints, $selectedItems, $term = null)
	{
		//first get the related model and fetch the field's options
		$config = App::make('itemconfig');
		$model = $config->model;
		$relatedModel = $model->{$field}()->getRelated();
		$info = Field::getOptions($field, $config, $type);

		//if we can't find the field, return an empty array
		if (!$info)
		{
			return array();
		}

		//set up the field object
		$info = Field::get($field, $info, $config, false);

		//make sure we're grouping by the model's id
		$query = $relatedModel::groupBy($relatedModel->getTable().'.'.$relatedModel->getKeyName())->getQuery();

		//set up the selects
		$query->select(array(DB::raw($relatedModel->getTable().'.*')));

		//if selectedItems are provided, set them up as a proper array
		if ($selectedItems)
		{
			//if this isn't an array, set it up as one
			$selectedItems = is_array($selectedItems) ? $selectedItems : explode(',', $selectedItems);
		}
		else
		{
			$selectedItems = array();
		}

		//if this is an autocomplete field, check if there is a search term. If not, just return the selected items
		if ($info->autocomplete && !$term)
		{
			if (sizeof($selectedItems))
			{
				$query->whereIn($relatedModel->getTable().'.'.$relatedModel->getKeyName(), $selectedItems);

				//if this is a hmabt and a sort field is set, order it by the sort field
				if ($info->multipleValues && $info->sortField)
				{
					$query->orderBy($info->sortField);
				}
				//otherwise order it by the name field
				else
				{
					$query->orderBy($info->nameField);
				}

				return static::formatOptions($relatedModel, $info, $query->get());
			}
			else
			{
				return array();
			}
		}

		//if there are constraints
		if (sizeof($info->constraints))
		{
			//iterate over the constraints
			foreach ($info->constraints as $key => $relationshipName)
			{
				//now that we're looping through the constraints, check to see if this one was supplied
				if (isset($constraints[$key]) && $constraints[$key] && sizeof($constraints[$key]))
				{
					//constrain the query
					$info->applyConstraints($query, $model, $key, $relationshipName, $constraints[$key]);
				}
			}
		}

		//if there is a search term, limit the result set by that term
		if ($term)
		{
			//set up the wheres
			foreach ($info->searchFields as $search)
			{
				$query->where(DB::raw($search), 'LIKE', '%'.$term.'%');
			}

			//exclude the currently-selected items if there are any
			if (count($selectedItems))
			{
				$query->whereNotIn($relatedModel->getTable().'.'.$relatedModel->getKeyName(), $selectedItems);
			}

			//set up the limits
			$query->take($info->numOptions + count($selectedItems));
		}

		//finally we can return the options
		return static::formatOptions($relatedModel, $info, $query->get());
	}

	/**
	 * Takes an eloquent result array and turns it into an options array that can be used in the UI
	 *
	 * @param  Eloquent 	$model
	 * @param  Relationship $info
	 * @param  array 		$results
	 *
	 * @return array
	 */
	public static function formatOptions($model, $info, $results)
	{
		return array_map(function($m) use ($info, $model)
		{
			return array(
				'id' => $m->{$model->getKeyName()},
				'text' => $m->{$info->nameField},
			);
		}, $results);
	}
}