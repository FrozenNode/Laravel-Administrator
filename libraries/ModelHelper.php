<?php
namespace Admin\Libraries;

use \Config;
use \DateTime;
use \DB;
use Admin\Libraries\Fields\Field;

class ModelHelper {


	/**
	 * List of possible field types in the edit array. this will grow to be more complex in time...
	 */
	static $fieldTypes = array('text', 'relation', 'currency', 'date', 'time', 'datetime' );

	/**
	 * List of possible field types in the edit array. this will grow to be more complex in time...
	 */
	static $relationshipTypes = array('relation_belongs_to', 'relation_has_one', 'relation_has_many', 'relation_has_many_and_belongs_to');

	/**
	 * List of fields that are min/max filter types
	 */
	static $minMaxTypes = array('currency', 'date', 'datetime', 'time');

	/**
	 * List of possible related object class names
	 */
	static $relationshipBase = 'Laravel\\Database\\Eloquent\\Relationships\\';


	/**
	 * Gets an instance of the supplied model given the id
	 *
	 * @param string	$modelName
	 * @param id		$id
	 *
	 * @return object|null	$model
	 * object with data => if the id exists
	 * new object => if id doesn't exist
	 * null => if there is no model by that name
	 */
	public static function getModel($modelName, $id = false)
	{
		//first instantiate a blank version of this object
		$classname = Config::get('administrator::administrator.models.'.$modelName.'.model', '');

		if (!class_exists($classname))
		{
			return null;
		}

		//get an empty model to work with and its included columns
		$emptyModel = static::getModelInstance($modelName);
		$columns = Column::getColumns($emptyModel);
		$editFields = Field::getEditFields($emptyModel);

		//make sure the edit fields are included
		foreach ($editFields['objectFields'] as $field => $obj)
		{
			if (!$obj->relationship && !array_key_exists($field, $columns['includedColumns']))
			{
				$columns['includedColumns'][$field] = $emptyModel->table().'.'.$field;
			}
		}

		//get the model
		$model = $classname::find($id, $columns['includedColumns']);

		if (!$model)
		{
			$model = $emptyModel;
		}
		else if ($model->exists)
		{
			//make sure the relationships are loaded
			foreach ($editFields['objectFields'] as $field => $info)
			{
				if ($info->relationship)
				{
					//get all existing values for this relationship
					if ($relatedItems = $model->{$field}()->get())
					{
						$relationsArray = array();

						//iterate over the items
						foreach ($relatedItems as $item)
						{
							//if this is a mutliple-value type (i.e. HasMany, HasManyAndBelongsTo), make sure this is an array
							if ($info->multipleValues)
							{
								$relationsArray[] = $item->{$item::$key};
							}
							else
							{
								$model->{$field} = $item->{$item::$key};
							}
						}

						//if $relationsArray isn't empty, it means we should set the value on the model
						if (!empty($relationsArray))
						{
							$model->{$field} = $relationsArray;
						}

					}
				}
			}
		}

		return $model;
	}

	/**
	 * Gets an instance of the supplied model
	 *
	 * @param string		$modelName
	 *
	 * @return object|null	$model
	 */
	public static function getModelInstance($modelName)
	{
		//first instantiate the object
		$classname = Config::get('administrator::administrator.models.'.$modelName.'.model', '');;

		if (class_exists($classname))
		{
			return new $classname();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets all necessary fields
	 *
	 * @param string		$modelName
	 *
	 * @return object|null	$model
	 */
	public static function getAllModelData($modelName)
	{
		//first instantiate the object
		$classname = static::$namespace.$modelName;

		if (class_exists($classname))
		{
			return new $classname();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Deletes a model and all of its relationships
	 *
	 * @param object		$model
	 *
	 * @return bool
	 */
	public static function deleteModel(&$model)
	{
		//instead of deleting relationships, run the user-supplied before_delete function
		if (method_exists($model, 'before_delete'))
		{
			$model->before_delete();
		}

		//then delete the model
		return $model->delete();
	}

	/**
	 * Gets the filters for the given model
	 *
	 * @param object	$model
	 *
	 * @return array
	 */
	public static function getFilters($model)
	{
		//get the model's edit fields
		$filters = array();

		//if the filters option is set, use it
		if (isset($model->filters) && count($model->filters) > 0)
		{
			foreach ($model->filters as $field => $info)
			{
				if ($fieldObject = Field::get($field, $info, $model))
				{
					$filters[$fieldObject->field] = $fieldObject->toArray();
				}
			}
		}

		return $filters;
	}

	/**
	 * Helper that builds a results array (with results and pagination info)
	 *
	 * @param object	$model
	 * @param array		$sortOptions (with 'field' and 'direction' keys)
	 * @param array		$filters (see getFilters helper for the value types)
	 */
	public static function getRows($model, $sortOptions, $filters = null)
	{
		//get the columns and sort options
		$columns = Column::getColumns($model, false);
		$sort = Sort::get($model, $sortOptions['field'], $sortOptions['direction']);

		//get things going by grouping the set
		$query = $model::group_by($model->table().'.'.$model::$key);

		//set up initial array states for the selects
		$selects = array(DB::raw($model->table().'.'.$model::$key), DB::raw($model->table().'.*'));

		//then we set the filters
		if ($filters && is_array($filters))
		{
			foreach ($filters as $filter)
			{
				if (!$fieldObject = Field::get($filter['field'], $filter, $model))
				{
					continue;
				}

				$fieldObject->filterQuery($query, $model);
			}
		}

		//determines if the sort should have the table prefixed to it
		$sortOnTable = true;

		//iterate over the columns to check if we need to join any values or add any extra columns
		foreach ($columns['columns'] as $field => $column)
		{
			//if this is a related column, we'll need to add some joins
			$column->filterQuery($query, $selects, $model);

			//if this is a related field or
			if ( ($column->isRelated || $column->select) && $column->field === $sort->field)
			{
				$sortOnTable = false;
			}
		}

		//if the sort is on the model's table, prefix the table name to it
		if ($sortOnTable)
		{
			$sort->field = $model->table().'.'.$sort->field;
		}

		//if there is a global per page limit set, make sure the paginator uses that
		$per_page = $model->per_page() ? $model->per_page() : 20;
		$global_per_page = Config::get('administrator::administrator.global_per_page', NULL);

		if ($global_per_page && is_numeric($global_per_page))
		{
			$per_page = $global_per_page;
		}

		/**
		 * We need to do our own pagination since there is a bug (!!!!!!!!!!!!!!) in the L3 paginator when using groupings :(
		 * When L4 is released, this problem will go away and we'll be able to use the paginator again
		 * Trust me, I understand how ghetto this is. I also understand that it may not work too well on other drivers. Let me know...
		 */

		//first get the sql sans selects
		$sql = $query->table->grammar->select($query->table);

		//then we need to round out the inner select
		$sql = "SELECT {$model->table()}.{$model::$key} " . $sql;

		//then wrap the inner table and perform the count
		$sql = "SELECT COUNT({$model::$key}) AS aggregate FROM ({$sql}) AS agg";

		//then perform the
		$results = $query->table->connection->query($sql, $query->table->bindings);
		$num_rows = $results[0]->aggregate;
		$page = (int) \Input::get('page', 1);

		//now we need to limit and offset the rows in remembrance of our dear lost friend paginate()
		$query->take($per_page);
		$query->skip($per_page * ($page - 1));

		//order the set by the model table's id
		$query->order_by($sort->field, $sort->direction);

		//then retrieve the rows
		$rows = $query->distinct()->get($selects);
		$results = array();

		//convert the resulting set into arrays
		foreach ($rows as $item)
		{
			//iterate over the included and related columns
			$onTableColumns = array_merge($columns['includedColumns'], $columns['relatedColumns']);
			$arr = array();

			foreach ($onTableColumns as $field => $col)
			{
				$arr[$field] = $item->get_attribute($field);
			}

			//then grab the computed, unsortable columns
			foreach ($columns['computedColumns'] as $col)
			{
				$arr[$col] = $item->{$col};
			}

			$results[] = $arr;
		}

		return array(
			'page' => $page,
			'last' => ceil($num_rows/$per_page),
			'total' => $num_rows,
			'results' => $results,
		);
	}

	/**
	 * Prepare a model for saving given a post input array
	 *
	 * @param object	$model
	 *
	 * @return false|object
	 */
	public static function fillModel(&$model)
	{
		$editFields = Field::getEditFields($model);

		//run through the edit fields to see if we need to set relationships
		foreach ($editFields['objectFields'] as $field => $info)
		{
			if (!$info->external)
			{
				$info->fillModel($model, \Input::get($field, NULL));
			}
			else
			{
				unset($model->attributes[$field]);
			}
		}
	}

	/**
	 * After a model has been saved, this is called to save the relationships
	 *
	 * @param object	$model
	 *
	 * @return false|object
	 */
	public static function saveRelationships(&$model)
	{
		$editFields = Field::getEditFields($model);

		//run through the edit fields to see if we need to set relationships
		foreach ($editFields['objectFields'] as $field => $info)
		{
			if ($info->external)
			{
				$info->fillModel($model, \Input::get($field, NULL));
			}
		}
	}
}