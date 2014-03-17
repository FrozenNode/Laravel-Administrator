<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Fields\Field;

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
		'options_filter' => 'callable',
		'constraints' => 'array',
	);

	/**
	 * Builds a few basic options
	 */
	public function build()
	{
		parent::build();

		$options = $this->suppliedOptions;
		$model = $this->config->getDataModel();
		$relationship = $model->{$options['field_name']}();

		//set the search fields to the name field if none exist
		$searchFields = $this->validator->arrayGet($options, 'search_fields');
		$nameField = $this->validator->arrayGet($options, 'name_field', $this->defaults['name_field']);
		$options['search_fields'] = empty($searchFields) ? array($nameField) : $searchFields;

		//determine if this is a self-relationship
		$options['self_relationship'] = $relationship->getRelated()->getTable() === $model->getTable();

		//make sure the options filter is set up
		$options['options_filter'] = $this->validator->arrayGet($options, 'options_filter') ?: function() {};

		//set up and check the constraints
		$this->setUpConstraints($options);

		//load up the relationship options
		$this->loadRelationshipOptions($options);

		$this->suppliedOptions = $options;
	}

	/**
	 * Sets up the constraints for a relationship field if provided. We do this so we can assume later that it will just work
	 *
	 * @param  array 		$options
	 *
	 * @return  void
	 */
	public function setUpConstraints(&$options)
	{
		$constraints = $this->validator->arrayGet($options, 'constraints');
		$model = $this->config->getDataModel();

		//set up and check the constraints
		if (sizeof($constraints))
		{
			$validConstraints = array();

			//iterate over the constraints and only include the valid ones
			foreach ($constraints as $field => $rel)
			{
				//check if the supplied values are strings and that their methods exist on their respective models
				if (is_string($field) && is_string($rel) && method_exists($model, $field))
				{
					$validConstraints[$field] = $rel;
				}
			}

			$options['constraints'] = $validConstraints;
		}
	}

	/**
	 * Loads the relationship options and sets the options option if load_relationships is true
	 *
	 * @param  array 		$options
	 *
	 * @return  void
	 */
	public function loadRelationshipOptions(&$options)
	{
		//if we want all of the possible items on the other model, load them up, otherwise leave the options empty
		$items = array();
		$model = $this->config->getDataModel();
		$relationship = $model->{$options['field_name']}();
		$relatedModel = $relationship->getRelated();

		if ($this->validator->arrayGet($options, 'load_relationships'))
		{
			//if a sort field was supplied, order the results by it
			if ($optionsSortField = $this->validator->arrayGet($options, 'options_sort_field'))
			{
				$optionsSortDirection = $this->validator->arrayGet($options, 'options_sort_direction', $this->defaults['options_sort_direction']);

				$query = $relatedModel->orderBy($this->db->raw($optionsSortField), $optionsSortDirection);
			}
			//otherwise just pull back an unsorted list
			else
			{
				$query = $relatedModel->newQuery();
			}

			//run the options filter
			$options['options_filter']($query);

			//get the items
			$items = $query->get();
		}
		//otherwise if there are relationship items, we need them in the initial options list
		else if ($relationshipItems = $relationship->get())
		{
			$items = $relationshipItems;
		}

		//map the options to the options property where array('id': [key], 'text': [nameField])
		$nameField = $this->validator->arrayGet($options, 'name_field', $this->defaults['name_field']);
		$keyField = $relatedModel->getKeyName();
		$options['options'] = $this->mapRelationshipOptions($items, $nameField, $keyField);
	}

	/**
	 * Maps the relationship options to an array with 'id' and 'text' keys
	 *
	 * @param array		$items
	 * @param string	$nameField
	 * @param string	$keyField
	 *
	 * @return array
	 */
	public function mapRelationshipOptions($items, $nameField, $keyField)
	{
		$result = array();

		foreach ($items as $option)
		{
			$result[] = array(
				'id' => $option->{$keyField},
				'text' => strval($option->{$nameField})
			);
		}

		return $result;
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