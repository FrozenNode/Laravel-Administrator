<?php
namespace Admin\Libraries\Fields\Relationships;

use Admin\Libraries\Fields\Field;

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
	 * The symbol to use in front of the number
	 *
	 * @var string
	 */
	public $table = '';

	/**
	 * The number of decimal places after the number
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
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param Eloquent 		$model
	 */
	public function __construct($field, $info, $model)
	{
		parent::__construct($field, $info, $model);

		//get an instance of the relationship object
		$relationship = $model->{$field}();

		//get the name field option
		$this->nameField = array_get($info, 'name_field', $this->nameField);
		$this->autocomplete = array_get($info, 'autocomplete', $this->autocomplete);
		$this->numOptions = array_get($info, 'num_options', $this->numOptions);
		$this->searchFields = array_get($info, 'search_fields', array($this->nameField));
		$this->selfRelationship = $relationship->model->table() === $model->table();

		//set up and check the constraints
		$this->setUpConstraints($info, $model);

		//if we want all of the possible items on the other model, load them up, otherwise leave the options empty
		$options = array();

		if (array_get($info, 'load_relationships', false))
		{
			$options = $relationship->model->all();
		}
		//otherwise if there are relationship items, we need them in the initial options list
		else if ($relationshipItems = $relationship->get())
		{
			$options = $relationshipItems;
		}

		$nameField = $this->nameField;

		//map the options to the options property where array([key]: int, [name_field]: string)
		$this->options = array_map(function($m) use ($nameField, $model)
		{
			return array(
				$m::$key => $m->{$m::$key},
				$nameField => $m->{$nameField},
			);
		}, $options);
	}

	/**
	 * Sets up the constraints for a relationship field if provided. We do this so we can assume later that it will just work
	 *
	 * @param  array 		$info
	 * @param  Eloquent		$model
	 * @param  Relationship	$relationship
	 *
	 * @return  void
	 */
	private function setupConstraints($info, $model)
	{
		$constraints = array_get($info, 'constraints', $this->constraints);

		//set up and check the constraints
		if (is_array($constraints) && sizeof($constraints))
		{
			$this->constraints = array();

			//iterate over the constraints and only include the valid ones
			foreach ($constraints as $field => $rel)
			{
				//check if the supplied values are strings and that their methods exist on their respective models
				if (is_string($field) && is_string($rel) && method_exists($model, $field))
				{
					$this->constraints[$field] = $rel;
				}
			}
		}
	}

	/**
	 * Constrains a query object with this item's relation to a third model
	 *
	 * @param Query		$query
	 * @param Eloquent	$model
	 * @param string	$key //the relationship name on this model
	 * @param string	$relationshipName //the relationship name on the constraint model
	 * @param array		$constraints
	 *
	 * @return void
	 */
	public function applyConstraints(&$query, $model, $key, $relationshipName, $constraints)
	{
		//first we get the other model and the relationship field on it
		$relatedModel = $model->{$this->field}()->model;
		$otherModel = $model->{$key}()->model;
		$otherField = Field::get($relationshipName, array('type' => 'relationship'), $otherModel, false);

		$otherField->constrainQuery($query, $relatedModel, $constraints);
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
		$arr['options'] = $this->options;
		$arr['selfRelationship'] = $this->selfRelationship;
		$arr['autocomplete'] = $this->autocomplete;
		$arr['num_options'] = $this->numOptions;
		$arr['search_fields'] = $this->searchFields;
		$arr['constraints'] = $this->constraints;

		return $arr;
	}
}