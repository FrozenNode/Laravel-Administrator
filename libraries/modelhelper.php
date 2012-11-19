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
		$classname = Config::get('administrator::administrator.models.'.$modelName.'.model', '');;

		if (!class_exists($classname))
		{
			return null;
		}

		//get an empty model to work with and its included columns
		$emptyModel = static::getModelInstance($modelName);
		$columns = static::getColumns($emptyModel);
		$editFields = static::getEditFields($emptyModel);

		//make sure the edit fields are included
		foreach ($editFields['dataModel'] as $field => $val)
		{
			if (!array_key_exists($field, $columns['includedColumns']))
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
	 * Gets the model's columns
	 *
	 * @param object	$model
	 * @param bool		$toArray
	 *
	 * @return array(
	 *			'columns' => array(detailed..),
	 *			'includedColumns' => array(field => full_column_name, ...)),
	 *			'computedColumns' => array(key, key, key)
	 */
	 public static function getColumns($model, $toArray = true)
	 {
	 	$return = array(
	 		'columns' => array(),
	 		'includedColumns' => array(),
	 		'computedColumns' => array(),
	 		'relatedColumns' => array(),
	 	);

	 	if (isset($model->columns) && count($model->columns) > 0)
		{
			$columns = array();

			foreach ($model->columns as $field => $column)
			{
				//get the column object
				if (!$columnObject = Column::get($field, $column, $model))
				{
					continue;
				}

				//if $toArray is true, add the column as an array. otherwise add the column object
				if ($toArray)
				{
					$return['columns'][$columnObject->field] = $columnObject->toArray();
				}
				else
				{
					$return['columns'][$columnObject->field] = $columnObject;
				}

				//categorize the columns
				if ($columnObject->isRelated)
				{
					$return['relatedColumns'][$columnObject->field] = $columnObject->field;

					if ($fk = $columnObject->relationshipField->foreignKey)
					{
						$return['includedColumns'][$fk] = $model->table().'.'.$fk;
					}
				}
				else if ($columnObject->isComputed)
				{
					$return['computedColumns'][$columnObject->field] = $columnObject->field;
				}
				else
				{
					$return['includedColumns'][$columnObject->field] = $model->table().'.'.$columnObject->field;
				}
			}
		}
		else
		{
			//throw exception!
		}

		//make sure the table key is included
		if (!array_get($return['includedColumns'], $model::$key))
		{
			$return['includedColumns'][$model::$key] = $model->table().'.'.$model::$key;
		}

		return $return;
	}

	/**
	 * Gets the model's edit fields
	 *
	 * @param object	$model
	 *
	 * @return array
	 */
	public static function getEditFields($model)
	{
		$return = array(
			'objectFields' => array(),
			'arrayFields' => array(),
			'dataModel' => array(),
		);

		if (isset($model->edit) && count($model->edit) > 0)
		{
			foreach ($model->edit as $field => $info)
			{
				//if this field can be properly set up, put it into the edit fields array
				if ($fieldObject = Field::get($field, $info, $model))
				{
					$return['objectFields'][$fieldObject->field] = $fieldObject;
					$return['arrayFields'][$fieldObject->field] = $fieldObject->toArray();
				}
			}
		}

		//add the id field, which will be uneditable, but part of the data model
		$return['arrayFields']['id'] = 0;

		//set up the data model
		foreach ($return['arrayFields'] as $field => $info)
		{
			if (is_array($info) || is_a($info, 'Field'))
			{
				$return['dataModel'][$field] = $model->$field;
			}
			else
			{
				$return['dataModel'][$field] = $info;
			}
		}

		return $return;
	}


	/**
	 * Gets the sort options for a model
	 *
	 * @param object	$model
	 * @param array		$includedColumns //simple array of column keys that are legitimate to be sorted
	 *
	 * @return array
	 */
	public static function getSortOptions($model, $includedColumns = null)
	{
		$default = array(
			'field' => 'id',
			'direction' => 'asc',
		);

		//first get the included columns if they don't exist
		if (!isset($includedColumns) || count($includedColumns) === 0)
		{
			$columns = static::getColumns($model);
			$includedColumns = $columns['includedColumns'];
		}

		if (isset($model->sortOptions) && count($model->sortOptions) > 0)
		{
			//check if the column is valid, otherwise keep default
			if (isset($model->sortOptions['field']) && in_array($model->sortOptions['field'], $includedColumns))
			{
				$default['field'] = $model->sortOptions['field'];
			}

			//check if the direction is valid, otherwise keep default
			if (isset($model->sortOptions['direction']) && in_array($model->sortOptions['direction'], array('asc', 'desc')))
			{
				$default['direction'] = $model->sortOptions['direction'];
			}
		}

		return $default;
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
		$columns = ModelHelper::getColumns($model, false);
		$sortOptions = array_merge(ModelHelper::getSortOptions($model), $sortOptions);

		//get things going by grouping the set
		$rows = $model::group_by($model->table().'.'.$model::$key);

		//set up initial array states for the joins and selects
		$joins = array();
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

				$fieldObject->filterQuery($rows, $model);
			}
		}

		//determines if the sort should have the table prefixed to it
		$sortOnTable = true;

		//iterate over the columns to check if we need to join any values or add any extra columns
		foreach ($columns['columns'] as $field => $column)
		{
			//if this is a related column, we'll need to add some joins
			$column->filterQuery($rows, $selects, $model);

			//if this is a related field or
			if ( ($column->isRelated || $column->select) && $column->field === $sortOptions['field'])
			{
				$sortOnTable = false;
			}
		}

		//if the sort is on the model's table, prefix the table name to it
		if ($sortOnTable)
		{
			$sortOptions['field'] = $model->table().'.'.$sortOptions['field'];
		}

		//order the set by the model table's id
		$rows->order_by($sortOptions['field'], $sortOptions['direction']);

		//if there is a global per page limit set, make sure the paginator uses that
		$per_page = NULL;
		$global_per_page = Config::get('administrator::administrator.global_per_page', NULL);

		if ($global_per_page && is_numeric($global_per_page))
		{
			$per_page = $global_per_page;
		}

		//then retrieve the rows
		$rows = $rows->paginate($per_page, $selects);
		$results = array();

		//convert the resulting set into arrays
		foreach ($rows->results as $item)
		{
			$arr = array_intersect_key($item->to_array(), array_merge($columns['includedColumns'], $columns['relatedColumns']));

			foreach ($columns['computedColumns'] as $col)
			{
				$arr[$col] = $item->{$col};
			}

			$results[] = $arr;
		}

		return array(
			'page' => $rows->page,
			'last' => $rows->last,
			'total' => $rows->total,
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
		$editFields = static::getEditFields($model);

		//run through the edit fields to see if we need to set relationships
		foreach ($editFields['objectFields'] as $field => $info)
		{
			$info->fillModel($model, \Input::get($field, NULL));
		}
	}
}
