<?php
namespace Admin\Libraries;

use Admin\Libraries\Fields\Field;
use \DB;

/**
 * The Column class helps us construct columns from models. It can be used to derive column information from a model, or it can be
 * instantiated to hold information about any given column.
 */
class Column {

	/**
	 * The field name
	 *
	 * @var string
	 */
	public $field;

	/**
	 * The column title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The sort field that the column will use if it is sortable
	 *
	 * @var string
	 */
	public $sort_field = NULL;

	/**
	 * The string value of the relationship name
	 *
	 * @var string
	 */
	public $relationship = NULL;

	/**
	 * This string SQL Select statement if this is a relationship or a computed value of some kind
	 *
	 * @var string
	 */
	public $select = NULL;

	/**
	 * Determines if this column is sortable
	 *
	 * @var string
	 */
	public $sortable = true;

	/**
	 * Holds the Field object for the relationship
	 *
	 * @var bool
	 */
	public $relationshipField = NULL;

	/**
	 * Determines if this column is a related column
	 *
	 * @var bool
	 */
	public $isRelated = false;

	/**
	 * Determines if this column is a computed column (either a getter or a select was supplied)
	 *
	 * @var bool
	 */
	public $isComputed = false;

	/**
	 * Determines if this column is a normal field on this table
	 *
	 * @var bool
	 */
	public $isIncluded = false;



	/**
	 * The constructor takes a field, column array, and the associated Eloquent model
	 *
	 * @param int|string	$field 	//column key
	 * @param string|array	$column //column model
	 * @param Eloquent		$model 	//eloquent model
	 */
	public function __construct($field, $column, $model)
	{
		//check if this is a numeric key, then we'll have to set the column model as an empty array
		if (is_numeric($field))
		{
			$field = $column;
			$column = array();
		}

		//set the values
		$this->field = $field;
		$this->title = array_get($column, 'title', $field);
		$this->sort_field = array_get($column, 'sort_field', $field);
		$this->sortable = array_get($column, 'sortable', $this->sortable);
		$this->relationship = array_get($column, 'relationship');
		$this->select = array_get($column, 'select');
		$this->isRelated = array_get($column, 'isRelated', $this->isRelated);
		$this->isComputed = array_get($column, 'isComputed', $this->isComputed);
		$this->isIncluded = array_get($column, 'isIncluded', $this->isIncluded);
		$this->relationshipField = array_get($column, 'relationshipField', $this->relationshipField);
	}

	/**
	 * Takes a the key/value of the columns array and the associated model and returns an instance of the column or false
	 *
	 * @param string|int	$field 		//the key of the options array
	 * @param array|string	$column		//the value of the options array
	 * @param Eloquent 		$model 		//an instance of the Eloquent model
	 *
	 * @return false|Field object
	 */
	public static function get($field, $column, $model)
	{
		//if this is a numeric field, $column holds the field
		if (is_numeric($field))
		{
			$field = $column;
			$column = array();
		}

		//set up the $column array with the supplied or default values
		$column = array
		(
			'title' => array_get($column, 'title', $field),
			'sort_field' => array_get($column, 'sort_field', $field),
			'relationship' => array_get($column, 'relationship'),
			'select' => array_get($column, 'select'),
			'sortable' => true, //for now...
		);

		//if the relation option is set, we'll set up the column array using the select
		if ($column['relationship'])
		{
			if (!method_exists($model, $column['relationship']) || !$column['select'])
			{
				return false;
			}

			//now we'll need to grab a relation field to see what its foreign table is
			if (!$relationshipField = Field::get($column['relationship'], array('type' => 'relationship'), $model, false))
			{
				return false;
			}

			//if this is a belongs_to, we need to set up the proper aliased select replacement
			if (!$relationshipField->external)
			{
				$selectTable = $field.'_'.$relationshipField->table;
			}
			//else replace (:table) with the simple table name
			else
			{
				$selectTable = $relationshipField->table;
			}

			$column['select'] = str_replace('(:table)', $selectTable, $column['select']);
			$column['relationshipField'] = $relationshipField;
		}
		//if the supplied item is a getter, make this unsortable for the moment
		else if (method_exists($model, 'get_'.$field) && $field === $column['sort_field'])
		{
			$column['sortable'] = false;
		}

		//however, if this is not a relation and the select option was supplied, str_replace the select option and make it sortable again
		if (!$column['relationship'] && $column['select'])
		{
			$column['select'] = str_replace('(:table)', $model->table(), $column['select']);
			$column['sortable'] = true;
		}

		//now we do some final organization to categorize these columns (useful later in the sorting)
		if ($column['relationship'])
		{
			$column['isRelated'] = true;
		}
		else if (method_exists($model, 'get_'.$field) || $column['select'])
		{
			$column['isComputed'] = true;
		}
		else
		{
			$column['isIncluded'] = true;
		}

		//now we can instantiate the object
		return new static($field, $column, $model);
	}

	/**
	 * Adds selects to a query
	 *
	 * @param Query 	$query
	 * @param array 	$selects
	 * @param Eloquent 	$model
	 *
	 * @return void
	 */
	public function filterQuery(&$query, &$selects, $model)
	{
		//add the select statement
		if ($this->select)
		{
			//if this is a related field, we have to set up a fancy select because of issues with grouping
			if ($this->isRelated)
			{
				$where = '';
				$fieldTable = $this->relationshipField->table;

				switch ($this->relationshipField->type)
				{
					case 'belongs_to':
						$fieldTable = $this->field.'_'.$this->relationshipField->table;

						$where = $model->table().'.'.$this->relationshipField->foreignKey.
							' = '.
						$fieldTable.'.'.$this->relationshipField->column;
						break;
					case 'has_one':
					case 'has_many':
						$where = $model->table().'.'.$model::$key.
							' = '.
						$fieldTable.'.'.$this->relationshipField->column;
						break;
					case 'has_many_and_belongs_to':
						$where = $model->table().'.'.$model::$key.
							' = '.
						$this->relationshipField->column;
						break;
				}

				$selects[] = DB::raw("(SELECT ".$this->select."
										FROM ".$this->relationshipField->table." AS ".$fieldTable."
										WHERE ".$where.") AS ".$this->field);
			}
			else
			{
				$selects[] = DB::raw($this->select.' AS '.$this->field);
			}
		}

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
	 * Turn this column into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'title' => $this->title,
			'sort_field' => $this->sort_field,
			'relationship' => $this->relationship,
			'select' => $this->select,
			'sortable' => $this->sortable,
		);
	}

	/**
	 * Checks if a table is already joined to a query object
	 *
	 * @param Query		$query
	 * @param string	$table
	 *
	 * @return bool
	 */
	public static function isJoined($query, $table)
	{
		$tableFound = false;
		$joins = $query->table->joins;

		if ($joins)
		{
			//iterate over the joins to see if the table is there
			foreach ($joins as $join)
			{
				if ($join->table === $table)
				{
					return true;
				}
			}
		}

		return false;
	}
}