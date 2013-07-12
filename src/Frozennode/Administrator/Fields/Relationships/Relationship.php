<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Fields\Field;
use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

abstract class Relationship extends Field {


	/**
	 * This is used in setting up filters
	 *
	 * @var bool
	 */
	public $relationship = true;

	/**
	 * If this is true, the field is an external field (i.e. it's a relationship but not a belongs_to)
	 *
	 * @var bool
	 */
	public $external = true;

	/**
	 * The string to use to name the items on the other table
	 *
	 * @var string
	 */
	public $nameField = 'name';

	/**
	 * The column to use to sort the options from the other table
	 *
	 * @var string
	 */
	public $optionsSortField = false;

	/**
	 * The SQL sort direction to use for the options sort field
	 *
	 * @var string
	 */
	public $optionsSortDirection = 'ASC';

	/**
	 * The relationship table name
	 *
	 * @var string
	 */
	public $table = '';

	/**
	 * The relationship column
	 *
	 * @var string
	 */
	public $column = '';

	/**
	 * Foreign key value that is used on the local table
	 *
	 * @var bool|string
	 */
	public $foreignKey = false;

	/**
	 * This determines if there are potentially multiple related values (i.e. whether to use an array of items or just a single value)
	 *
	 * @var bool
	 */
	public $multipleValues = false;

	/**
	 * The array of items from which the user will be able to choose
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * If this is true, the field will start with no options and be an autocomplete
	 *
	 * @var bool
	 */
	public $selfRelationship = false;

	/**
	 * If this is true, the field will start with no options and be an autocomplete
	 *
	 * @var bool
	 */
	public $autocomplete = false;

	/**
	 * The number of options to display to a user when the autocomplete is turned on
	 *
	 * @var int
	 */
	public $numOptions = 10;

	/**
	 * The search fields on the other table to look for when autocomplete is on. If left empty, default is the name_field
	 *
	 * @var array
	 */
	public $searchFields = array();

	/**
	 * The constraining relationships. If this has a value
	 *
	 * @var array
	 */
	public $constraints = array();

	/**
	 * Create a new Relationship instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		parent::__construct($validator, $config, $db, $options);

		//put the model into a variable so we can call it statically
		$model = $this->config->getDataModel();

		//get an instance of the relationship object
		$relationship = $model->{$this->field}();

		//get the various options
		$this->nameField = $this->validator->arrayGet($options, 'name_field', $this->nameField);
		$this->optionsSortField = $this->validator->arrayGet($options, 'options_sort_field', $this->nameField);
		$this->optionsSortDirection = $this->validator->arrayGet($options, 'options_sort_direction', $this->optionsSortDirection);
		$this->autocomplete = $this->validator->arrayGet($options, 'autocomplete', $this->autocomplete);
		$this->numOptions = $this->validator->arrayGet($options, 'num_options', $this->numOptions);
		$this->searchFields = $this->validator->arrayGet($options, 'search_fields', array($this->nameField));
		$this->selfRelationship = $relationship->getRelated()->getTable() === $model->getTable();

		//set up and check the constraints
		$this->setUpConstraints($options);

		//if we want all of the possible items on the other model, load them up, otherwise leave the options empty
		$items = array();

		if ($this->validator->arrayGet($options, 'load_relationships', false))
		{
			//if a sort field was supplied, order the results by it
			if ($this->optionsSortField)
			{
				$items = $relationship->getRelated()->orderBy($this->db->raw($this->optionsSortField), $this->optionsSortDirection)->get();
			}
			//otherwise just pull back an unsorted list
			else
			{
				$items = $relationship->getRelated()->get();
			}
		}
		//otherwise if there are relationship items, we need them in the initial options list
		else if ($relationshipItems = $relationship->get())
		{
			$items = $relationshipItems;
		}

		$nameField = $this->nameField;

		//map the options to the options property where array('id': [key], 'text': [nameField])
		foreach ($items as $option)
		{
			$this->options[] = array(
				'id' => $option->id,
				'text' => $option->{$nameField}
			);
		}

	}

	/**
	 * Sets up the constraints for a relationship field if provided. We do this so we can assume later that it will just work
	 *
	 * @param  array 		$options
	 *
	 * @return  void
	 */
	public function setupConstraints($options)
	{
		$constraints = $this->validator->arrayGet($options, 'constraints', $this->constraints);

		//set up and check the constraints
		if (is_array($constraints) && sizeof($constraints))
		{
			$this->constraints = array();

			//iterate over the constraints and only include the valid ones
			foreach ($constraints as $field => $rel)
			{
				//check if the supplied values are strings and that their methods exist on their respective models
				if (is_string($field) && is_string($rel) && method_exists($this->config->getDataModel(), $field))
				{
					$this->constraints[$field] = $rel;
				}
			}
		}
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['table'] = $this->table;
		$arr['column'] = $this->column;
		$arr['foreignKey'] = $this->foreignKey;
		$arr['name_field'] = $this->nameField;
		$arr['external'] = $this->external;
		$arr['options'] = $this->options;
		$arr['selfRelationship'] = $this->selfRelationship;
		$arr['autocomplete'] = $this->autocomplete;
		$arr['num_options'] = $this->numOptions;
		$arr['search_fields'] = $this->searchFields;
		$arr['constraints'] = $this->constraints;

		return $arr;
	}
}