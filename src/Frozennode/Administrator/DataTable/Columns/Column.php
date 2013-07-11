<?php
namespace Frozennode\Administrator\DataTable\Columns;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;
use Frozennode\Administrator\Fields\Field;
use Illuminate\Support\Facades\App;
use \Exception;

/**
 * The Column class helps us construct columns from models. It can be used to derive column information from a model, or it can be
 * instantiated to hold information about any given column.
 */
class Column {

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The config instance
	 *
	 * @var Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The config instance
	 *
	 * @var Illuminate\Database\DatabaseManager
	 */
	protected $db;

	/**
	 * The options array
	 *
	 * @var array
	 */
	protected $options;

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
	 * The immediate relationship object for this column
	 *
	 * @var Relationship
	 */
	public $relationshipObject = NULL;

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
	 * Determines if this column is a computed column (either an accessor or a select was supplied)
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
	 * Determines if this column is a HasOne, HasMany, or BelongsToMany
	 *
	 * @var bool
	 */
	public $external = false;

	/**
	 * Determines if this column is a BelongsToMany
	 *
	 * @var bool
	 */
	public $belongsToMany = false;

	/**
	 * The table prefix
	 *
	 * @var string
	 */
	public $tablePrefix = '';

	/**
	 * The class name of a BelongsTo relationship
	 *
	 * @var string
	 */
	const BELONGS_TO = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';

	/**
	 * The class name of a BelongsToMany relationship
	 *
	 * @var string
	 */
	const BELONGS_TO_MANY = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany';

	/**
	 * The class name of a HasMany relationship
	 *
	 * @var string
	 */
	const HAS_MANY = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';

	/**
	 * The class name of a HasOne relationship
	 *
	 * @var string
	 */
	const HAS_ONE = 'Illuminate\\Database\\Eloquent\\Relations\\HasOne';

	/**
	 * Create a new action Factory instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager 				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		//set the config, and then validate it
		$this->config = $config;
		$this->validator = $validator;
		$this->db = $db;
		$this->options = $options;
		$this->field = $options['column_name'];
	}

	/**
	 * Builds the necessary fields on the object
	 */
	public function build()
	{
		$this->title = $this->validator->arrayGet($this->options, 'title', $this->field);
		$this->sort_field = $this->validator->arrayGet($this->options, 'sort_field', $this->field);
		$this->relationship = $this->validator->arrayGet($this->options, 'relationship');
		$this->select = $this->validator->arrayGet($this->options, 'select');
		$this->isRelated = $this->validator->arrayGet($this->options, 'isRelated', $this->isRelated);
		$this->isComputed = $this->validator->arrayGet($this->options, 'isComputed', $this->isComputed);
		$this->isIncluded = $this->validator->arrayGet($this->options, 'isIncluded', $this->isIncluded);
		$this->tablePrefix = $this->db->getTablePrefix();
		$output = $this->validator->arrayGet($this->options, 'output');
		$this->output = is_string($output) ? $output : $this->output;
		$model = $this->config->getDataModel();

		//if the relation option is set, we'll set up the column array using the select
		if ($this->relationship)
		{
			$this->validateRelationship();
		}
		//if the supplied item is an accessor, make this unsortable for the moment
		else if (method_exists($model, camel_case('get_'.$this->field.'_attribute')) && $this->field === $this->sort_field)
		{
			$this->sortable = false;
		}

		//however, if this is not a relation and the select option was supplied, str_replace the select option and make it sortable again
		if (!$this->relationship && $this->select)
		{
			$this->select = str_replace('(:table)', $this->tablePrefix . $model->getTable(), $this->select);
			$this->sortable = true;
		}

		//now we do some final organization to categorize these columns (useful later in the sorting)
		$this->organizeColumn();
	}

	/**
	 * Validates relationship columns
	 *
	 * @return void
	 */
	public function validateRelationship()
	{
		$model = $this->config->getDataModel();

		//split the string up into an array on the . symbol
		if ($nested = $this->getNestedRelationships($this->relationship))
		{
			$relevant_name = $nested['pieces'][sizeof($nested['pieces'])-1];
			$relevant_model = $nested['models'][sizeof($nested['models'])-2];
			$this->nested = $nested;

			$relationship = $relevant_model->{$relevant_name}();
			$this->table = $relationship->getRelated()->getTable();
			$this->foreignKey = $relationship->getForeignKey();
			$selectTable = $this->field . '_' . $this->tablePrefix . $this->table;
		}
		//if we couldn't make a belongsTo nest out of it, check if it's a BTM, HM, or HO
		else if (method_exists($model, $this->relationship))
		{
			$relationship = $model->{$this->relationship}();

			if (is_a($relationship, self::BELONGS_TO_MANY) || is_a($relationship, self::HAS_MANY) || is_a($relationship, self::HAS_ONE))
			{
				$relevant_name = $this->relationship;
				$relevant_model = $model;
				$this->external = true;
				$selectTable = $this->field . '_' . $this->tablePrefix . $model->{$this->relationship}()->getRelated()->getTable();
			}

			if (is_a($relationship, self::BELONGS_TO_MANY))
			{
				$this->belongsToMany = true;
			}
		}

		//if the relevant model isn't set, we couldn't find the relationship
		if (!isset($relevant_model))
		{
			throw new \InvalidArgumentException("The '" . $this->field . "' column in your " . $this->config->getOption('name') .
					" model configuration needs to be either a relationship method name or a sequence of belongsTo relationship method names connected with a '.'");
		}

		//check if a 'select' option was provided
		if (!$this->select)
		{
			throw new \InvalidArgumentException("You must provide a valid 'select' option for the " . $this->field . " relationship column in your " .
							$this->config->getOption('name') . " model configuration.");
		}

		//set the relationship object so we can use it later
		$this->relationshipObject = $relationship;

		//replace the (:table) with the generated $selectTable
		$this->select = str_replace('(:table)', $selectTable, $this->select);
	}

	/**
	 * Converts the relationship key
	 *
	 * @param string		$name 	//the relationship name
	 *
	 * @return false|array('models' => array(), 'pieces' => array())
	 */
	public function getNestedRelationships($name)
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
				$models[] = $this->config->getDataModel();
			}

			//if the model method doesn't exist for any of the pieces along the way, exit out
			if (!method_exists($models[$i], $rel) || !is_a($models[$i]->{$rel}(), self::BELONGS_TO))
			{
				return false;
			}

			//we don't need the model of the last item
			$models[] = $models[$i]->{$rel}()->getRelated();
		}

		return array('models' => $models, 'pieces' => $pieces);
	}

	/**
	 * Organizes this column
	 *
	 * @return void
	 */
	public function organizeColumn()
	{
		if ($this->relationship)
		{
			$this->isRelated = true;
		}
		else if (method_exists($this->config->getDataModel(), camel_case('get_'.$this->field.'_attribute')) || $this->select)
		{
			$this->isComputed = true;
		}
		else
		{
			$this->isIncluded = true;
		}
	}

	/**
	 * Adds selects to a query
	 *
	 * @param array 	$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$selects)
	{
		$model = $this->config->getDataModel();

		//add the select statement
		if ($this->select)
		{
			//if this is a related field, we have to set up a fancy select because of issues with grouping
			if ($this->isRelated)
			{
				$where = '';

				//now we must tediously build the joins if there are nested relationships (should only be for belongs_to fields)
				$joins = '';

				switch (get_class($this->relationshipObject))
				{
					case self::BELONGS_TO:
						$num_pieces = sizeof($this->nested['pieces']);

						if ($num_pieces > 1)
						{
							for ($i = 1; $i < $num_pieces; $i++)
							{
								$model = $this->nested['models'][$i];
								$relationship = $model->{$this->nested['pieces'][$i]}();
								$relationship_model = $relationship->getRelated();
								$table = $this->tablePrefix . $relationship_model->getTable();
								$alias = $this->field . '_' . $table;
								$last_alias = $this->field . '_' . $this->tablePrefix . $model->getTable();
								$joins .= ' LEFT JOIN ' . $table . ' AS ' . $alias .
											' ON ' . $alias . '.' . $relationship->getRelated()->getKeyName() .
												' = ' . $last_alias . '.' . $relationship->getForeignKey();
							}
						}

						$first_model = $this->nested['models'][0];
						$first_piece = $this->nested['pieces'][0];
						$first_relationship = $first_model->{$first_piece}();
						$relationship_model = $first_relationship->getRelated();
						$from_table = $this->tablePrefix . $relationship_model->getTable();
						$field_table = $this->field . '_' . $from_table;

						$where = $this->tablePrefix.$first_model->getTable() . '.' . $first_relationship->getForeignKey() .
									' = ' .
									$field_table . '.' . $relationship_model->getKeyName();
						break;
					case self::BELONGS_TO_MANY:
						$relationship = $model->{$this->relationship}();
						$from_table = $this->tablePrefix.$model->getTable();
						$field_table = $this->field . '_' . $from_table;
						$other_table = $this->tablePrefix . $relationship->getRelated()->getTable();
						$other_alias = $this->field . '_' . $other_table;
						$other_model = $relationship->getRelated();
						$other_key = $other_model->getKeyName();
						$int_table = $this->tablePrefix . $relationship->getTable();
						$int_alias = $this->field . '_' . $int_table;
						$column1 = explode('.', $relationship->getForeignKey());
						$column1 = $column1[1];
						$column2 = explode('.', $relationship->getOtherKey());
						$column2 = $column2[1];
						$joins .= ' LEFT JOIN '.$int_table.' AS '.$int_alias.' ON '.$int_alias.'.'.$column1.' = '.$field_table.'.'.$model->getKeyName()
								.' LEFT JOIN '.$other_table.' AS '.$other_alias.' ON '.$other_alias.'.'.$other_key.' = '.$int_alias.'.'.$column2;

						$where = $this->tablePrefix . $model->getTable() . '.' . $model->getKeyName() . ' = ' . $int_alias . '.' . $column1;
						break;
					case self::HAS_ONE:
					case self::HAS_MANY:
						$relationship = $model->{$this->relationship}();
						$from_table = $this->tablePrefix . $relationship->getRelated()->getTable();
						$field_table = $this->field . '_' . $from_table;

						$where = $this->tablePrefix.$model->getTable() . '.' . $model->getKeyName() .
								' = ' .
								$field_table . '.' . $relationship->getPlainForeignKey();
						break;
				}

				$selects[] = $this->db->raw("(SELECT " . $this->select . "
										FROM " . $from_table." AS " . $field_table . ' ' . $joins . "
										WHERE " . $where . ") AS `" . $this->field . "`");
			}
			else
			{
				$selects[] = $this->db->raw($this->select . ' AS ' . $this->field);
			}
		}

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