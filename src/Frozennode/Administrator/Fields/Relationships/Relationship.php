<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Fields\Field;

abstract class Relationship extends Field {

	/**
	 * The default options for this field
	 *
	 * @var array
	 */
	protected $defaultOptions = [
		'relationship' => true,
		'external' => true,
		'name_field' => 'name',
		'options_sort_field' => false,
		'options_sort_direction' => 'ASC',
		'table' => '',
		'column' => '',
		'foreign_key' => false,
		'multiple_values' => false,
		'options' => [],
		'self_relationship' => false,
		'autocomplete' => false,
		'num_options' => 10,
		'search_fields' => [],
		'constraints' => [],
		'load_relationships' => false,
	);

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaultOptions = [];

	/**
	 * The rules for this field
	 *
	 * @var array
	 */
	protected $rules = [
		'name_field' => 'string',
		'sort_field' => 'string',
		'options_sort_field' => 'string',
		'options_sort_direction' => 'string',
		'num_options' => 'integer|min:0',
		'search_fields' => 'array',
		'options_filter' => 'callable',
		'constraints' => 'array',
	];

	/**
	 * Builds a few basic options
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		$options = parent::buildOptions($options);

		$model = $this->config->getDataModel();
		$relationship = $model->{$options['field_name']}();

		//set the search fields to the name field if none exist
		$searchFields = array_get($options, 'search_fields');
		$nameField = array_get($options, 'name_field', $this->defaultOptions['name_field']);
		$options['search_fields'] = empty($searchFields) ? [$nameField] : $searchFields;

		//determine if this is a self-relationship
		$options['self_relationship'] = $relationship->getRelated()->getTable() === $model->getTable();

		//make sure the options filter is set up
		$options['options_filter'] = array_get($options, 'options_filter', function() {});

		//set up and check the constraints
		$this->setUpConstraints($options);

		//load up the relationship options
		$this->loadRelationshipOptions($options);

		return $options;
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
		$constraints = array_get($options, 'constraints');
		$model = $this->config->getDataModel();

		//set up and check the constraints
		if (sizeof($constraints))
		{
			$validConstraints = [];

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
		$items = [];
		$model = $this->config->getDataModel();
		$relationship = $model->{$options['field_name']}();
		$relatedModel = $relationship->getRelated();

		if (array_get($options, 'load_relationships'))
		{
			//if a sort field was supplied, order the results by it
			if ($optionsSortField = array_get($options, 'options_sort_field'))
			{
				$optionsSortDirection = array_get($options, 'options_sort_direction', $this->defaultOptions['options_sort_direction']);

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

		//map the options to the options property where ['id': [key], 'text': [nameField]]
		$nameField = array_get($options, 'name_field', $this->defaultOptions['name_field']);
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
		$result = [];

		foreach ($items as $option)
		{
			$result[] = [
				'id' => $option->{$keyField},
				'text' => strval($option->{$nameField})
			];
		}

		return $result;
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaultOptions()
	{
		return array_merge(parent::getDefaultOptions(), $this->relationshipDefaultOptions);
	}

	/**
	 * Checks if a table is already joined to a query object
	 *
	 * @param Query		$query
	 * @param string	$table
	 *
	 * @return bool
	 */
	public function isJoined($query, $table)
	{
		$tableFound = false;
		$query = is_a($query, 'Illuminate\Database\Query\Builder') ? $query : $query->getQuery();

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
}