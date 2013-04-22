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
	 * This holds the rendered output of this column.
	 *
	 * @var string
	 */
	public $output = '(:value)';

	/**
	 * Determines if this column is sortable
	 *
	 * @var bool
	 */
	public $sortable = true;

	/**
	 * Holds the Field object for the relationship
	 *
	 * @var Field
	 */
	public $relationshipField = NULL;

	/**
	 * Holds the nested relationship string pieces and models
	 *
	 * @var array
	 */
	public $nested = array();

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
	 */
	public function __construct($field, $column)
	{
		//check if this is a numeric key, then we'll have to set the column model as an empty array
		if (is_numeric($field))
		{
			$field = $column;
			$column = array();
		}

		//output value...reorganize this
		$output = array_get($column, 'output');

		//set the values
		$this->field = $field;
		$this->title = array_get($column, 'title', $field);
		$this->sort_field = array_get($column, 'sort_field', $field);
		$this->sortable = array_get($column, 'sortable', $this->sortable);
		$this->relationship = array_get($column, 'relationship');
		$this->select = array_get($column, 'select');
		$this->nested = array_get($column, 'nested', $this->nested);
		$this->isRelated = array_get($column, 'isRelated', $this->isRelated);
		$this->isComputed = array_get($column, 'isComputed', $this->isComputed);
		$this->isIncluded = array_get($column, 'isIncluded', $this->isIncluded);
		$this->output = is_string($output) ? $output : $this->output;
		$this->relationshipField = array_get($column, 'relationshipField', $this->relationshipField);
	}

	/**
	 * Takes a the key/value of the columns array and the associated model and returns an instance of the column or false
	 *
	 * @param string|int	$field 		//the key of the options array
	 * @param array|string	$column		//the value of the options array
	 * @param ModelConfig	$config		//this model's config
	 *
	 * @return false|Field object
	 */
	public static function get($field, $column, $config)
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
			'output' => array_get($column, 'output'),
		);

		//if the relation option is set, we'll set up the column array using the select
		if ($column['relationship'])
		{
			//split the string up into an array on the . symbol
			if (!$nested = static::getNestedRelationships($config->model, $column['relationship']))
			{
				return false;
			}

			if (!$column['select'])
			{
				return false;
			}

			//now we'll need to grab a relation field to see what its foreign table is
			$relevant_name = $nested['pieces'][sizeof($nested['pieces'])-1];
			$relevant_model = $nested['models'][sizeof($nested['models'])-2];

			if (!$relationshipField = Field::get($relevant_name, array('type' => 'relationship'), $relevant_model, false))
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

			$column['nested'] = $nested;
			$column['select'] = str_replace('(:table)', $selectTable, $column['select']);
			$column['relationshipField'] = $relationshipField;
		}
		//if the supplied item is a getter, make this unsortable for the moment
		else if (method_exists($config->model, 'get_'.$field) && $field === $column['sort_field'])
		{
			$column['sortable'] = false;
		}

		//however, if this is not a relation and the select option was supplied, str_replace the select option and make it sortable again
		if (!$column['relationship'] && $column['select'])
		{
			$column['select'] = str_replace('(:table)', $config->model->table(), $column['select']);
			$column['sortable'] = true;
		}

		//now we do some final organization to categorize these columns (useful later in the sorting)
		if ($column['relationship'])
		{
			$column['isRelated'] = true;
		}
		else if (method_exists($config->model, 'get_'.$field) || $column['select'])
		{
			$column['isComputed'] = true;
		}
		else
		{
			$column['isIncluded'] = true;
		}

		//now we can instantiate the object
		return new static($field, $column);
	}

	/**
	 * Converts the relationship key
	 *
	 * @param Eloquent		$top_model
	 * @param string		$name 	//the relationship name
	 *
	 * @return false|array('models' => array(), 'relationships' => array(), 'pieces' => array())
	 */
	private static function getNestedRelationships($top_model, $name)
	{
		$pieces = explode('.', $name);
		$models = array();
		$num_pieces = sizeof($pieces);

		//iterate over the relationships to see if they're all valid
		foreach ($pieces as $i => $rel)
		{
			//if this is the first item, then the model is the config's model
			if ($i === 0)
			{
				$models[] = $top_model;
			}

			//if the model method doesn't exist for any of the pieces along the way, exit out
			if (!method_exists($models[$i], $rel) || !is_a($models[$i]->{$rel}(), 'Laravel\Database\Eloquent\Relationships\Belongs_To'))
			{
				return false;
			}

			//we don't need the model of the last item
			$models[] = $models[$i]->{$rel}()->model;
		}

		return array('models' => $models, 'pieces' => $pieces);
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
				$from_table = $field_table = $this->relationshipField->table;

				//now we must tediously build the joins if there are nested relationships (should only be for belongs_to fields)
				$joins = '';
				$num_pieces = sizeof($this->nested['pieces']);

				if ($num_pieces > 1)
				{
					for ($i = 1; $i < $num_pieces; $i++)
					{
						$model = $this->nested['models'][$i];
						$relationship = $model->{$this->nested['pieces'][$i]}();
						$relationship_model = $relationship->model;
						$table = $relationship_model->table();
						$alias = $this->field.'_'.$table;
						$last_alias = $this->field.'_'.$model->table();
						$joins .= ' LEFT JOIN '.$table.' AS '.$alias.' ON '.$alias.'.'.$relationship->model->key().' = '.$last_alias.'.'.$relationship->foreign;
					}
				}

				switch ($this->relationshipField->type)
				{
					case 'belongs_to':
						$first_model = $this->nested['models'][0];
						$first_piece = $this->nested['pieces'][0];
						$first_relationship = $first_model->{$first_piece}();
						$relationship_model = $first_relationship->model;
						$from_table = $relationship_model->table();
						$field_table = $this->field.'_'.$from_table;

						$where = $first_model->table().'.'.$first_relationship->foreign.
							' = '.
						$field_table.'.'.$relationship_model::$key;
						break;
					case 'has_one':
					case 'has_many':
						$where = $model->table().'.'.$model::$key.
							' = '.
						$field_table.'.'.$this->relationshipField->column;
						break;
					case 'has_many_and_belongs_to':
						$where = $model->table().'.'.$model::$key.
							' = '.
						$this->relationshipField->column;
						break;
				}

				$selects[] = DB::raw("(SELECT ".$this->select."
										FROM ".$from_table." AS ".$field_table.' '.$joins."
										WHERE ".$where.") AS ".$this->field);
			}
			else
			{
				$selects[] = DB::raw($this->select.' AS '.$this->field);
			}
		}

	}

	/**
	 * Gets a model's columns given the a model's config
	 *
	 * @param ModelConfig		$config
	 *
	 * @return array(
	 *			'columns' => array(detailed..),
	 *			'includedColumns' => array(field => full_column_name, ...)),
	 *			'computedColumns' => array(key, key, key)
	 */
	 public static function getColumns($config)
	 {
	 	$model = $config->model;
	 	$return = array(
	 		'columns' => array(),
	 		'columnArrays' => array(),
	 		'columnObjects' => array(),
	 		'includedColumns' => array(),
	 		'computedColumns' => array(),
	 		'relatedColumns' => array(),
	 	);

	 	//check if there are columns to iterate over
	 	if (count($config->columns) > 0)
		{
			$columns = array();

			foreach ($config->columns as $field => $column)
			{
				//get the column object
				if (!$columnObject = Column::get($field, $column, $config))
				{
					continue;
				}

				//save the column object with a $field-based key, as a simple array (to use in knockout), and as a simple array of arrays
				$return['columnObjects'][$field] = $columnObject;
				$return['columns'][] = $columnObject;
				$return['columnArrays'][] = $columnObject->toArray();

				//categorize the columns
				if ($columnObject->isRelated)
				{
					$return['relatedColumns'][$columnObject->field] = $columnObject->field;

					//if there are nested values, we'll want to grab the values slightly differently
					if (sizeof($columnObject->nested))
					{
						$fk = $columnObject->nested['models'][0]->{$columnObject->nested['pieces'][0]}()->foreign;
						$return['includedColumns'][$fk] = $model->table().'.'.$fk;
					}
					else if ($fk = $columnObject->relationshipField->foreignKey)
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
			throw new Exception("Administrator: you must provide a valid 'columns' array in each model's config");
		}

		//make sure the table key is included
		if (!array_get($return['includedColumns'], $model::$key))
		{
			$return['includedColumns'][$model::$key] = $model->table().'.'.$model::$key;
		}

		//make sure any belongs_to fields that aren't on the columns list are included
		$editFields = Field::getEditFields($config);

		foreach ($editFields['objectFields'] as $field => $info)
		{
			if (is_a($info, 'Admin\\Libraries\\Fields\\Relationships\\BelongsTo'))
			{
				$return['includedColumns'][$info->foreignKey] = $model->table().'.'.$info->foreignKey;
			}
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
			'field' => $this->field,
			'title' => $this->title,
			'sort_field' => $this->sort_field,
			'relationship' => $this->relationship,
			'select' => $this->select,
			'sortable' => $this->sortable,
			'output' => $this->output,
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

	/**
	 * Takes a column output string and renders the column with it (replacing '(:value)' with the column's field value)
	 *
	 * @param string	$output
	 *
	 * @return string
	 */
	public function renderOutput($value)
	{
		return str_replace('(:value)', $value, $this->output);
	}
}