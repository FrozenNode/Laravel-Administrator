<?php
namespace Frozennode\Administrator;

use Frozennode\Administrator\Fields\Field;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use \Exception;

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
	 * The full class name of a belongsTo relationship
	 *
	 * @var string
	 */
	public static $belongsToClass = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';

	/**
	 * The full class name of a hasManyAndBelongsTo relationship
	 *
	 * @var string
	 */
	public static $hasManyAndBelongsToClass = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany';

	/**
	 * The full class name of a hasManyAndBelongsTo relationship
	 *
	 * @var string
	 */
	public static $hasManyClass = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';

	/**
	 * The full class name of a hasManyAndBelongsTo relationship
	 *
	 * @var string
	 */
	public static $hasOneClass = 'Illuminate\\Database\\Eloquent\\Relations\\HasOne';



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
	 * @param ModelConfig	$config
	 *
	 * @return false|Field object
	 */
	public static function get($field, $column, $config = null)
	{
		$config = $config ? $config : App::make('itemconfig');

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
			$rel = $column['relationship'];

			//split the string up into an array on the . symbol
			if ($nested = static::getNestedRelationships($config->model, $rel))
			{
				$relevant_name = $nested['pieces'][sizeof($nested['pieces'])-1];
				$relevant_model = $nested['models'][sizeof($nested['models'])-2];
				$column['nested'] = $nested;
			}
			//if we couldn't make a belongsTo nest out of it, check if it's a HMABT, HM, or HO
			else if (method_exists($config->model, $rel))
			{
				$relationship = $config->model->{$rel}();

				//HMABT, HM, HO
				if (is_a($relationship, static::$hasManyAndBelongsToClass) || is_a($relationship, static::$hasManyClass)
																						|| is_a($relationship, static::$hasOneClass))
				{
					$relevant_name = $rel;
					$relevant_model = $config->model;
				}
				else
				{
					throw new Exception("Administrator: the relationship provided for the " . $field . " column is invalid");
				}
			}
			else
			{
				throw new Exception("Administrator: the relationship provided for the " . $field . " column is invalid");
			}

			//check if a 'select' option was provided
			if (!$column['select'])
			{
				throw new Exception("Administrator: you must provide a 'select' option for the " . $field . " relationship column");
			}

			//now we'll need to grab a relation field to see what its foreign table is
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
				$selectTable = $field.'_'.$config->model->{$rel}()->getRelated()->getTable();
			}

			$column['select'] = str_replace('(:table)', $selectTable, $column['select']);
			$column['relationshipField'] = $relationshipField;
		}
		//if the supplied item is a getter, make this unsortable for the moment
		else if (method_exists($config->model, camel_case('get_'.$field.'_attribute')) && $field === $column['sort_field'])
		{
			$column['sortable'] = false;
		}

		//however, if this is not a relation and the select option was supplied, str_replace the select option and make it sortable again
		if (!$column['relationship'] && $column['select'])
		{
			$column['select'] = str_replace('(:table)', $config->model->getTable(), $column['select']);
			$column['sortable'] = true;
		}

		//now we do some final organization to categorize these columns (useful later in the sorting)
		if ($column['relationship'])
		{
			$column['isRelated'] = true;
		}
		else if (method_exists($config->model, camel_case('get_'.$field.'_attribute')) || $column['select'])
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
			if (!method_exists($models[$i], $rel) || !is_a($models[$i]->{$rel}(), static::$belongsToClass))
			{
				return false;
			}

			//we don't need the model of the last item
			$models[] = $models[$i]->{$rel}()->getRelated();
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

				switch ($this->relationshipField->type)
				{
					case 'belongs_to':
						$num_pieces = sizeof($this->nested['pieces']);

						if ($num_pieces > 1)
						{
							for ($i = 1; $i < $num_pieces; $i++)
							{
								$model = $this->nested['models'][$i];
								$relationship = $model->{$this->nested['pieces'][$i]}();
								$relationship_model = $relationship->getRelated();
								$table = $relationship_model->getTable();
								$alias = $this->field.'_'.$table;
								$last_alias = $this->field.'_'.$model->getTable();
								$joins .= ' LEFT JOIN '.$table.' AS '.$alias.' ON '.$alias.'.'.$relationship->getRelated()->getKeyName().' = '.$last_alias.'.'.$relationship->getForeignKey();
							}
						}

						$first_model = $this->nested['models'][0];
						$first_piece = $this->nested['pieces'][0];
						$first_relationship = $first_model->{$first_piece}();
						$relationship_model = $first_relationship->getRelated();
						$from_table = $relationship_model->getTable();
						$field_table = $this->field.'_'.$from_table;

						$where = $first_model->getTable().'.'.$first_relationship->getForeignKey().
							' = '.
						$field_table.'.'.$relationship_model->getKeyName();
						break;
					case 'has_one':
					case 'has_many':
						$field_table = $this->field . '_' . $from_table;

						$where = $model->getTable().'.'.$model->getKeyName().
							' = '.
						$field_table.'.'.$this->relationshipField->column;
						break;
					case 'has_many_and_belongs_to':
						$relationship = $model->{$this->relationship}();
						$from_table = $model->getTable();
						$field_table = $this->field.'_'.$from_table;
						$other_table = $relationship->getRelated()->getTable();
						$other_alias = $this->field.'_'.$other_table;
						$other_model = $relationship->getRelated();
						$other_key = $other_model->getKeyName();
						$int_table = $this->relationshipField->table;
						$int_alias = $this->field.'_'.$int_table;
						$column1 = explode('.', $this->relationshipField->column);
						$column1 = $column1[1];
						$column2 = explode('.', $this->relationshipField->column2);
						$column2 = $column2[1];
						$joins .= ' LEFT JOIN '.$int_table.' AS '.$int_alias.' ON '.$int_alias.'.'.$column1.' = '.$field_table.'.'.$model->getKeyName()
								.' LEFT JOIN '.$other_table.' AS '.$other_alias.' ON '.$other_alias.'.'.$other_key.' = '.$int_alias.'.'.$column2;

						$where = $model->getTable().'.'.$model->getKeyName().' = '.$int_alias.'.'.$column1;
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
	 public static function getColumns($config = null)
	 {
	 	$config = $config ? $config : App::make('itemconfig');
	 	$model = $config->model;
	 	$return = array(
	 		//simple list of the provided column objects
	 		'columns' => array(),
	 		//the same as above but in array form
	 		'columnArrays' => array(),
	 		//same as 'columns', but indexed on the field name
	 		'columnObjects' => array(),
	 		//columns that are on the model's table (i.e. not related or computed)
	 		'includedColumns' => array(),
	 		//columns that are 'computed' (either a getter or a select was supplied)
	 		'computedColumns' => array(),
	 		//relationship columns
	 		'relatedColumns' => array(),
	 	);

	 	//check if there are columns to iterate over
	 	if (count($config->columns) > 0)
		{
			$columns = array();

			foreach ($config->columns as $field => $column)
			{
				$noInfo = is_numeric($field);

				$field = $noInfo ? $column : $field;
				$column = $noInfo ? array() : $column;

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
						$fk = $columnObject->nested['models'][0]->{$columnObject->nested['pieces'][0]}()->getForeignKey();
						$return['includedColumns'][$fk] = $model->getTable().'.'.$fk;
					}
					else if ($fk = $columnObject->relationshipField->foreignKey)
					{
						$return['includedColumns'][$fk] = $model->getTable().'.'.$fk;
					}
				}
				else if ($columnObject->isComputed)
				{
					$return['computedColumns'][$columnObject->field] = $columnObject->field;
				}
				else
				{
					$return['includedColumns'][$columnObject->field] = $model->getTable().'.'.$columnObject->field;
				}
			}
		}
		else
		{
			throw new Exception("Administrator: you must provide a valid 'columns' array in each model's config");
		}

		//make sure the table key is included
		if (!array_get($return['includedColumns'], $model->getKeyName()))
		{
			$return['includedColumns'][$model->getKeyName()] = $model->getTable().'.'.$model->getKeyName();
		}

		//make sure any belongs_to fields that aren't on the columns list are included
		$editFields = Field::getEditFields($config);

		foreach ($editFields['objectFields'] as $field => $info)
		{
			if (is_a($info, 'Frozennode\\Administrator\\Fields\\Relationships\\BelongsTo'))
			{
				$return['includedColumns'][$info->foreignKey] = $model->getTable().'.'.$info->foreignKey;
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

		if ($query->joins)
		{
			//iterate over the joins to see if the table is there
			foreach ($query->joins as $join)
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