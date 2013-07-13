<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Fields\Field;
use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

abstract class Relationship extends Field {

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array(
		'relationship' => true,
		'external' => true,
		'name_field' => 'name',
		'options_sort_field' => false,
		'options_sort_direction' => 'ASC',
		'table' => '',
		'column' => '',
		'foreign_key' => false,
		'multiple_values' => false,
		'options' => array(),
		'self_relationship' => false,
		'autocomplete' => false,
		'num_options' => 10,
		'search_fields' => array(),
		'constraints' => array(),
		'load_relationships' => false,
	);

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = array();

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = array(
		'name_field' => 'string',
		'sort_field' => 'string',
		'options_sort_field' => 'string',
		'options_sort_direction' => 'string',
		'num_options' => 'integer|min:0',
		'search_fields' => 'array',
		'constraints' => 'array',
	);

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
		$relationship = $model->{$this->getOption('field_name')}();

		//set the search fields to the name field if none exist
		$searchFields = $this->getOption('search_fields');
		$this->userOptions['search_fields'] = empty($searchFields) ? array($this->getOption('name_field')) : $searchFields;

		//determine if this is a self-relationship
		$this->userOptions['self_relationship'] = $relationship->getRelated()->getTable() === $model->getTable();

		//set up and check the constraints
		$this->setUpConstraints($options);

		//load up the relationship options
		$this->loadRelationshipOptions($options);
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
		$constraints = $this->getOption('constraints');

		//set up and check the constraints
		if (sizeof($constraints))
		{
			$validConstraints = array();

			//iterate over the constraints and only include the valid ones
			foreach ($constraints as $field => $rel)
			{
				//check if the supplied values are strings and that their methods exist on their respective models
				if (is_string($field) && is_string($rel) && method_exists($this->config->getDataModel(), $field))
				{
					$validConstraints[$field] = $rel;
				}
			}

			$this->userOptions['constraints'] = $validConstraints;
		}
	}

	/**
	 * Loads the relationship options and sets the options option if load_relationships is true
	 *
	 * @param  array 		$options
	 *
	 * @return  void
	 */
	public function loadRelationshipOptions($options)
	{
		//if we want all of the possible items on the other model, load them up, otherwise leave the options empty
		$items = array();

		if ($this->getOption('load_relationships'))
		{
			//if a sort field was supplied, order the results by it
			if ($optionsSortField = $this->getOption('options_sort_field'))
			{
				$optionsSortDirection = $this->getOption('options_sort_direction');

				$items = $relationship->getRelated()->orderBy($this->db->raw($optionsSortField), $optionsSortDirection)->get();
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

		//map the options to the options property where array('id': [key], 'text': [nameField])
		$dataOptions = array();
		$nameField = $this->getOption('name_field');

		foreach ($items as $option)
		{
			$dataOptions[] = array(
				'id' => $option->id,
				'text' => $option->{$nameField}
			);
		}

		$this->userOptions['options'] = $dataOptions;
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		$defaults = parent::getDefaults();

		return array_merge($defaults, $this->relationshipDefaults);
	}
}