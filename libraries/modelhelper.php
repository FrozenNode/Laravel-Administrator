<?php 
namespace Admin\Libraries;

use \Config;
use \DateTime;

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
		
		foreach ($editFields['editFields'] as $field => $ef)
		{
			if ($ef['type'] === 'relation_belongs_to')
			{
				$columns['includedColumns'][] = $emptyModel->{$field}()->foreign;
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
			foreach ($editFields['editFields'] as $field => $ef)
			{
				if (in_array($ef['type'], static::$relationshipTypes))
				{
					//get the relations
					if ($relations = $model->{$field}()->get())
					{
						$relations_arr = array();
						
						//if they're there, iterate over them and store them as fields that the JS can understand
						foreach ($relations as $rel)
						{
							if ($ef['type'] === 'relation_belongs_to' || $ef['type'] === 'relation_has_one')
							{
								//if it's a single, just set the first item as the value
								$model->{$field} = $rel->{$rel::$key};
							}
							else if ($ef['type'] === 'has_many' || $ef['type'] === 'relation_has_many_and_belongs_to')
							{
								$relations_arr[] = $rel->{$rel::$key};
							}
						}
						
						if (!empty($relations_arr))
						{
							$model->{$field} = $relations_arr;
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
	 * 
	 * @return array('columns' => array(detailed..), 'includedColumns' => array(key, key, key))
	 */
	 public static function getColumns($model)
	 {
	 	$return = array();
		
	 	if (isset($model->columns) && count($model->columns) > 0)
		{
			$columns = array();
			
			foreach ($model->columns as $key => $column)
			{
				//if the key is numeric, use the value string and set the default values.
				if (is_numeric($key))
				{
					$key = $column;
					$column = array('title' => $key);
				}
				else
				{
					$column = array(
						'title' => isset($column['title']) ? $column['title'] : $key
					);
				}
				
				$columns[$key] = $column;
			}
			
			$return['columns'] = $columns;
		}
		else
		{
			//grab all the attribute keys and use them as the key/title
			$attribute_keys = array_keys($model->attributes);
			$columns = array();
			
			foreach ($attribute_keys as $attr)
			{
				$columns[$attr] = array('title' => $attr);
			}
			
			$return['columns'] = $columns;
		}
		
		//now set the "includedColumns"
		$return['includedColumns'] = array();
		$return['computedColumns'] = array();
		
		foreach ($columns as $key => $col) {
			if (method_exists($model, 'get_'.$key))
			{
				$return['computedColumns'][] = $key;
			}
			else
			{
				$return['includedColumns'][] = $key;
			}
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
		$return = array();
		
		if (isset($model->edit) && count($model->edit) > 0)
		{
			$fields = array();
			
			foreach ($model->edit as $field => $info)
			{
				//if this field can be properly set up, put it into the edit fields array
				if ($field_data = static::getFieldData($model, $field, $info))
				{
					$fields[$field_data['field']] = $field_data['info'];
				}
			}
		}
		else
		{
			//grab all the attribute keys and use them as the key/title
			$attribute_keys = array_keys($model->attributes);
			$fields = array();
			
			foreach ($attribute_keys as $attr)
			{
				//we don't want to include the key
				if ($attr !== $model::$key) {
					$fields[$attr] = array(
						'title' => $attr,
						'type' => static::$fieldTypes[0],
					);
				}
			}
		}
		
		//add the id field, which will be uneditable, but part of the data model
		$fields['id'] = 0;
		
		//set up the data model
		$dataModel = array();
		
		foreach ($fields as $key => $val)
		{
			if (is_array($val))
			{
				$dataModel[$key] = $model->$key;
			}
			else
			{
				$dataModel[$key] = $val;
			}
		}
		
		return array('editFields' => $fields, 'dataModel' => $dataModel);
	}

	/**
	 * Fills up a field with all of its required information given a model, key, and field
	 * 
	 * @param object		$model
	 * @param string|int	$key
	 * @param string|array	$field
	 * 
	 * @return false|array
	 */
	public static function getFieldData($model, $field, $info)
	{
		if (is_numeric($field))
		{
			//if the key is numeric, use the value string and set the default values.
			$field = $info;
			
			$info = array(
				'title' => $field,
				'type' => static::$fieldTypes[0],
			);
		}
		else
		{
			//if the key is text, sort through the options to determine what to do with this field
			$info['type'] = isset($info['type']) && in_array($info['type'], static::$fieldTypes) ? $info['type'] : static::$fieldTypes[0];
			$info['title'] = isset($info['title']) ? $info['title'] : $field;
			
			//if it's a related field
			switch($info['type'])
			{
				//if this is a related field, check to see what kind of relation it is
				case 'relation':
				{
					//check if the related method exists on the model
					if (!method_exists($model, $field))
					{
						return false;
					}
					
					//now that we know the method exists, we can determine if it's multiple or single
					$related_model = $model->{$field}();
					
					//certain relationships need certain save/delete methods. For instance, a Belongs_To only stores the foreign key,
					//so we would need to do ( $model->{$related_model->foreign} = $some_int ) in order to save it.
					if (is_a($related_model, static::$relationshipBase.'Belongs_To'))
					{
						$info['type'] = 'relation_belongs_to';
					}
					else if (is_a($related_model, static::$relationshipBase.'Has_One'))
					{
						$info['type'] = 'relation_has_one';
					}
					else if (is_a($related_model, static::$relationshipBase.'Has_Many'))
					{
						$info['type'] = 'relation_has_many';
					}
					else if (is_a($related_model, static::$relationshipBase.'Has_Many_And_Belongs_To'))
					{
						$info['type'] = 'relation_has_many_and_belongs_to';
					}
					else
					{
						return false;
					}
					
					//set the title field
					$info['title_field'] = isset($info['title_field']) ? $info['title_field'] : 'name';
					
					//set the options
					$info['options'] = array_map(function($m) use ($info, $model)
					{ 
						return array(
							$model::$key => $m->{$model::$key},
							$info['title_field'] => $m->{$info['title_field']},
						);
					}, $related_model->model->all());

					break;
				}
				case 'currency':
				{
					$info['symbol'] = isset($info['symbol']) ? $info['symbol'] : '$';
					$info['decimals'] = isset($info['decimals']) ? $info['decimals'] : 2;
					break;
				}
				case 'date':
				{
					$info['date_format'] = isset($info['date_format']) ? $info['date_format'] : 'yy-mm-dd';
					break;
				}
				case 'time':
				{
					$info['time_format'] = isset($info['time_format']) ? $info['time_format'] : 'HH:mm';
					break;
				}
				case 'datetime':
				{
					$info['date_format'] = isset($info['date_format']) ? $info['date_format'] : 'yy-mm-dd';
					$info['time_format'] = isset($info['time_format']) ? $info['time_format'] : 'HH:mm';
					break;
				}
					
			}
		}
		
		return array('field' => $field, 'info' => $info);
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
		$fields = static::getEditFields($model);
		$filters = array();
		
		//if the filters option is set, use it
		if (isset($model->filters) && count($model->filters) > 0)
		{
			foreach ($model->filters as $field => $info)
			{
				$no_info = is_numeric($field);
				$field = $no_info ? $info : $field;
				
				//if this is the primary key, consider this an id filter
				if ($field === $model::$key)
				{
					$filter = array(
						'type' => 'id',
						'title' => isset($info['title']) ? $info['title'] : $field,
					);
				}
				//if this filter is not in the data model, then we can't use it
				else if (in_array($field, array_keys($fields['editFields'])))
				{
					$ef = $fields['editFields'][$field];

					//set up the filter as the edit fields array, then overwrite anything that is set by the user
					$filter = $ef;
					
					//if the title key is set, overwrite the existing one
					if (isset($info['title']))
					{
						$filter['title'] = $info['title'];
					}
				}
				//if this is a relation field, get the field data
				else if (!$no_info && $info['type'] === 'relation')
				{
					if ($field_data = static::getFieldData($model, $field, $info))
					{
						$filter = $field_data['info'];
					}
					else continue;
				}

				$filter['value'] = '';
				$filter['field'] = $field;
				
				$filters[] = $filter;
			}
		}
		else
		{	//otherwise use the data model
			foreach ($fields['dataModel'] as $field => $val)
			{
				$ef = $fields['editFields'][$field];
				$filter = array();
				
				//if this is the id field, set it up with the id filter
				if ($field === $model::$key)
				{
					$filters[] = array(
						'type' => 'id',
						'title' => $field,
						'field' => $field,
						'value' => ''
					);
					
					continue;
				}
				
				//set the type and title fields
				$filter = $ef;
				$filter['value'] = '';
				$filter['field'] = $field;
				
				$filters[] = $filter;
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
		$columns = ModelHelper::getColumns($model);
		$sortOptions = array_merge(ModelHelper::getSortOptions($model), $sortOptions);
		
		//sort the results
		$rows = $model::order_by($model->table().'.'.$sortOptions['field'], $sortOptions['direction']);
		
		//then we set the filters
		if ($filters && is_array($filters))
		{
			foreach ($filters as $filter)
			{
				//if there is no value in this filter, ignore it
				if (empty($filter['value']) || (is_string($filter['value']) && !trim($filter['value'])))
				{
					continue;
				}
				
				switch ($filter['type'])
				{
					case 'text':
						$rows->where($model->table().'.'.$filter['field'], 'LIKE', '%' . $filter['value'] . '%');
						break;
					case 'id':
						$rows->where($model->table().'.'.$filter['field'], '=', $filter['value']);
						break;
					case 'relation_belongs_to':
						$rows->where($model->{$filter['field']}()->foreign, 'LIKE', '%'.$filter['value'].'%');
						break;
					case 'relation_has_one':
					case 'relation_has_many':
						$relation_table = $model->{$filter['field']}()->table->from;
						$wheres = $model->{$filter['field']}()->table->wheres[0];
						
						$rows->join($relation_table, $model->table().'.'.$model::$key, '=', $relation_table.'.'.$wheres['column']);
						$rows->where_in($relation_table.'.id', (is_array($filter['value']) ? $filter['value'] : array($filter['value'])));
						break;
					case 'relation_has_many_and_belongs_to':
						$relation_table = $model->{$filter['field']}()->table->joins[0];
						$wheres = $model->{$filter['field']}()->table->wheres[0];
						
						//join the connecting table
						$rows->join($relation_table->table, $model->table().'.'.$model::$key, '=', $wheres['column']);
						$rows->where_in($relation_table->clauses[0]['column2'], $filter['value']);
						break;
				}
			}
		}
		
		//if there is a global per page limit set, make sure the paginator uses that
		$per_page = NULL;
		$global_per_page = Config::get('administrator::administrator.global_per_page', NULL);
		
		if ($global_per_page && is_numeric($global_per_page))
		{
			$per_page = $global_per_page;
		}
		
		//then retrieve the rows
		$rows = $rows->paginate($per_page);
		$results = array();
		
		//convert the resulting set into arrays
		foreach ($rows->results as $item)
		{
			$colKeys = array_combine($columns['includedColumns'], $columns['includedColumns']);
			$arr = array_intersect_key($item->to_array(), $colKeys);

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
		foreach ($editFields['editFields'] as $field => $ef)
		{
			//now we set the model fields depending on what type of edit fields they are
			switch ($ef['type'])
			{
				case 'text':
				case 'currency':
				{
					$model->{$field} = \Input::get($field, '');
					break;
				}
				case 'date':
				case 'time':
				case 'datetime':
				{
					$val = \Input::get($field, '');
					
					if (strtotime($val))
					{
						$model->{$field} = new DateTime($val);
					}
					
					break;
				}
				case 'relation_belongs_to':
				{
					$relation = $model->{$field}();
					$model->{$relation->foreign} = \Input::get($field, NULL);
					unset($model->attributes[$field]);
					break;
				}
				case 'relation_has_one':
				{
					
					break;
				}
				case 'relation_has_many':
				{
					
					break;
				}
				case 'relation_has_many_and_belongs_to':
				{
					$model->{$field}()->sync(\Input::get($field, array()));
					unset($model->attributes[$field]);
					break;
				}
			}
		}
	}
}
