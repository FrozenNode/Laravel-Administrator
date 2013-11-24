<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Factory {

	/**
	 * The valid field types and their associated classes
	 *
	 * @var array
	 */
	protected $fieldTypes = array(
		'key' => 'Frozennode\\Administrator\\Fields\\Key',
		'text' => 'Frozennode\\Administrator\\Fields\\Text',
		'textarea' => 'Frozennode\\Administrator\\Fields\\Text',
		'wysiwyg' => 'Frozennode\\Administrator\\Fields\\Text',
		'markdown' => 'Frozennode\\Administrator\\Fields\\Text',
		'password' => 'Frozennode\\Administrator\\Fields\\Password',
		'date' => 'Frozennode\\Administrator\\Fields\\Time',
		'time' => 'Frozennode\\Administrator\\Fields\\Time',
		'datetime' => 'Frozennode\\Administrator\\Fields\\Time',
		'number' => 'Frozennode\\Administrator\\Fields\\Number',
		'bool' => 'Frozennode\\Administrator\\Fields\\Bool',
		'enum' => 'Frozennode\\Administrator\\Fields\\Enum',
		'image' => 'Frozennode\\Administrator\\Fields\\Image',
		'file' => 'Frozennode\\Administrator\\Fields\\File',
		'color' => 'Frozennode\\Administrator\\Fields\\Color',

		//relationships
		'belongs_to' => 'Frozennode\\Administrator\\Fields\\Relationships\\BelongsTo',
		'belongs_to_many' => 'Frozennode\\Administrator\\Fields\\Relationships\\BelongsToMany',
		'has_one' => 'Frozennode\\Administrator\\Fields\\Relationships\\HasOne',
		'has_many' => 'Frozennode\\Administrator\\Fields\\Relationships\\HasMany',

	);

	/**
	 * The base string for the relationship classes
	 */
	protected $relationshipBase = 'Illuminate\\Database\\Eloquent\\Relations\\';

	/**
	 * The base string for the relationship classes
	 */
	protected $settingsFieldExclusions = array('key', 'belongs_to', 'belongs_to_many', 'has_one', 'has_many');

	/**
	 * The validator instance
	 *
	 * @var \Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The config interface instance
	 *
	 * @var \Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The config instance
	 *
	 * @var \Illuminate\Database\DatabaseManager
	 */
	protected $db;

	/**
	 * The compiled filters objects
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * The compiled filters arrays
	 *
	 * @var array
	 */
	protected $filtersArrays = array();

	/**
	 * The compiled edit fields array
	 *
	 * @var array
	 */
	protected $editFields;

	/**
	 * The edit field objects as arrays
	 *
	 * @var array
	 */
	protected $editFieldsArrays;

	/**
	 * The edit field data model
	 *
	 * @var array
	 */
	protected $dataModel;

	/**
	 * Create a new model Config instance
	 *
	 * @param \Frozennode\Administrator\Validator 				$validator
	 * @param \Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param \Illuminate\Database\DatabaseManager 				$db
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db)
	{
		$this->validator = $validator;
		$this->config = $config;
		$this->db = $db;
	}

	/**
	 * Makes a field given an array of options
	 *
	 * @param mixed 	$name
	 * @param mixed 	$options
	 * @param boolean	$loadRelationships	//determines whether or not to load the relationships
	 *
	 * @return mixed
	 */
	public function make($name, $options, $loadRelationships = true)
	{
		//make sure the options array has all the proper default values
		$options = $this->prepareOptions($name, $options, $loadRelationships);

		return $this->getFieldObject($options);
	}

	/**
	 * Instantiates a field object
	 *
	 * @param array 	$options
	 * @param boolean 	$loadRelationships
	 *
	 * @return Frozennode\Administrator\Fields\Field
	 */
	public function getFieldObject($options)
	{
		$class = $this->getFieldTypeClass($options['type']);
		return new $class($this->validator, $this->config, $this->db, $options);
	}

	/**
	 * Gets the class name for a field type
	 *
	 * @param string 	$type
	 *
	 * @return string
	 */
	public function getFieldTypeClass($type)
	{
		return $this->fieldTypes[$type];
	}

	/**
	 * Sets up an options array with the required base values
	 *
	 * @param mixed 	$name
	 * @param mixed 	$options
	 * @param boolean	$loadRelationships	//determines whether or not to load the relationships
	 *
	 * @return array
	 */
	public function prepareOptions($name, $options, $loadRelationships = true)
	{
		//set the options array to the format we need
		$options = $this->validateOptions($name, $options);

		//make sure the 'title' option is set
		$options['title'] = isset($options['title']) ? $options['title'] : $options['field_name'];

		//ensure the type is set and then check that the field type exists
		$this->ensureTypeIsSet($options);

		//set the proper relationship options
		$this->setRelationshipType($options, $loadRelationships);

		//check that the type is a valid field class
		$this->checkTypeExists($options);

		return $options;
	}

	/**
	 * Validates an options array item. This could be a string $name and array $options, or a positive integer $name and string $options.
	 *
	 * @param mixed		$name
	 * @param mixed		$options
	 *
	 * @return array
	 */
	public function validateOptions($name, $options)
	{
		if (is_string($options))
		{
			$name = $options;
			$options = array();
		}

		//if the name is not a string or the options is not an array at this point, throw an error because we can't do anything with it
		if (!is_string($name) || !is_array($options))
		{
			throw new \InvalidArgumentException("One of the fields in your " . $this->config->getOption('name') . " configuration file is invalid");
		}

		//in any case, make sure the 'column_name' option is set
		$options['field_name'] = $name;

		return $options;
	}

	/**
	 * Ensures that the type option is set.
	 *
	 * @param array		$options
	 *
	 * @return void
	 */
	public function ensureTypeIsSet(array &$options)
	{
		//if the 'type' option hasn't been set
		if (!isset($options['type']))
		{
			//if this is a model and the field is equal to the primary key name, set it as a key field
			if ($this->config->getType() === 'model' && $options['field_name'] === $this->config->getDataModel()->getKeyName())
			{
				$options['type'] = 'key';
			}
			//otherwise set it to the default 'text'
			else
			{
				$options['type'] = 'text';
			}
		}
	}

	/**
	 * Ensures that a relationship field is valid
	 *
	 * @param array		$options
	 * @param bool		$loadRelationships
	 *
	 * @return void
	 */
	public function setRelationshipType(array &$options, $loadRelationships)
	{
		//if this is a relationship
		if ($this->validator->arrayGet($options, 'type') === 'relationship')
		{
			//get the right key based on the relationship in the model
			$options['type'] = $this->getRelationshipKey($options['field_name']);

			//if we should load the relationships, set the option
			$options['load_relationships'] = $loadRelationships && !$this->validator->arrayGet($options, 'autocomplete', false);
		}
	}

	/**
	 * Check to see if the type is valid
	 *
	 * @param array 	$options
	 *
	 * @return void
	 */
	public function checkTypeExists(array &$options)
	{
		//if an improper value was supplied
		if (!array_key_exists($options['type'], $this->fieldTypes))
		{
			throw new \InvalidArgumentException('The ' . $options['type'] . ' field type in your ' . $this->config->getOption('name') . ' configuration file is not valid');
		}

		//if this is a settings page and a field was supplied that is excluded
		if ($this->config->getType() === 'settings' && in_array($options['type'], $this->settingsFieldExclusions))
		{
			throw new \InvalidArgumentException('The ' . $options['type'] . ' field in your ' .
							$this->config->getOption('name') . ' settings page cannot be used on a settings page');
		}
	}

	/**
	 * Given a field name, returns the type key or false
	 *
	 * @param string 	$field  	the field type to check
	 *
	 * @return string|false
	 */
	public function getRelationshipKey($field)
	{
		$model = $this->config->getDataModel();
		$invalidArgument = new \InvalidArgumentException("The '" . $field . "' relationship field you supplied for " .
								$this->config->getOption('name') . " is not a valid relationship method name on the supplied Eloquent model");

		//check if the related method exists on the model
		if (!method_exists($model, $field))
		{
			throw $invalidArgument;
		}

		//now that we know the method exists, we can determine if it's multiple or single
		$related_model = $model->{$field}();

		//check if this is a valid relationship object, and return the appropriate key
		if (is_a($related_model, $this->relationshipBase.'BelongsTo'))
		{
			return 'belongs_to';
		}
		else if (is_a($related_model, $this->relationshipBase.'BelongsToMany'))
		{
			return 'belongs_to_many';
		}
		else if (is_a($related_model, $this->relationshipBase.'HasOne'))
		{
			return 'has_one';
		}
		else if (is_a($related_model, $this->relationshipBase.'HasMany'))
		{
			return 'has_many';
		}
		else
		{
			throw $invalidArgument;
		}
	}

	/**
	 * Given a field name, this returns the field object from the edit fields array
	 *
	 * @param string	 	$field
	 *
	 * @return \Frozennode\Administrator\Fields\Field
	 */
	public function findField($field)
	{
		$fields = $this->getEditFields();

		//return either the Field object or throw an InvalidArgumentException
		if (!isset($fields[$field]))
		{
			throw new \InvalidArgumentException("The " . $field . " field does not exist on the " . $this->config->getOption('name') . " model");
		}

		return $fields[$field];
	}

	/**
	 * Given a field name, this returns the field object from the filters array
	 *
	 * @param string	 	$field
	 *
	 * @return \Frozennode\Administrator\Fields\Field
	 */
	public function findFilter($field)
	{
		$filters = $this->getFilters();

		//return either the Field object or throw an InvalidArgumentException
		if (!isset($filters[$field]))
		{
			throw new \InvalidArgumentException("The " . $field . " filter does not exist on the " . $this->config->getOption('name') . " model");
		}

		return $filters[$field];
	}

	/**
	 * Creates the edit fields as Field objects
	 *
	 * @param boolean 	$loadRelationships //if set to false, no relationship options will be loaded
	 * @param boolean 	$override //if set to true, the fields will be re-loaded, otherwise it will use the cached fields
	 *
	 * @return array
	 */
	public function getEditFields($loadRelationships = true, $override = false)
	{
		if (!sizeof($this->editFields) || $override)
		{
			$this->editFields = array();

			//iterate over each supplied edit field
			foreach ($this->config->getOption('edit_fields') as $name => $options)
			{
				$fieldObject = $this->make($name, $options, $loadRelationships);
				$this->editFields[$fieldObject->getOption('field_name')] = $fieldObject;
			}
		}

		return $this->editFields;
	}

	/**
	 * Gets the array version of the edit fields objects
	 *
	 * @param boolean 	$override 	//this will override the cached version if set to true
	 *
	 * @return array
	 */
	public function getEditFieldsArrays($override = false)
	{
		$return = array();

		foreach ($this->getEditFields(true, $override) as $fieldObject)
		{
			$return[$fieldObject->getOption('field_name')] = $fieldObject->getOptions();
		}

		//get the key field if this is a model page
		if ($this->config->getType() === 'model')
		{
			$this->fillKeyField($return);
		}

		return $return;
	}

	/**
	 * Gets the key field for a model for the getEditFieldsArrays
	 *
	 * @param array		$fields
	 *
	 * @return array
	 */
	public function fillKeyField(array &$fields)
	{
		$model = $this->config->getDataModel();
		$keyName = $model->getKeyName();

		//add the primary key field, which will be uneditable, but part of the data model
		if ($this->config->getType() === 'model' && !isset($fields[$keyName]))
		{

			$keyField = $this->make($keyName, array('visible' => false));
			$fields[$keyName] = $keyField->getOptions();
		}
	}

	/**
	 * Gets the data model given the edit fields
	 *
	 * @return array
	 */
	public function getDataModel()
	{
		$dataModel = array();
		$model = $this->config->getDataModel();

		foreach ($this->getEditFieldsArrays() as $name => $options)
		{
			//if this is a key, set it to 0
			if ($options['type'] === 'key')
			{
				$dataModel[$name] = 0;
			}
			else
			{
				//if this is a collection, convert it to an array
				if (is_a($model->$name, 'Illuminate\Database\Eloquent\Collection'))
				{
					$dataModel[$name] = $model->$name->toArray();
				}
				else
				{
					$dataModel[$name] = isset($options['value']) ? $options['value'] : null;
				}
			}
		}

		return $dataModel;
	}

	/**
	 * Gets the filters for the given model config
	 *
	 * @return array
	 */
	public function getFilters()
	{
		//get the model's filter fields
		$configFilters = $this->config->getOption('filters');

		//make sure that the filters array hasn't been created before and that there are supplied filters in the config
		if (!sizeof($this->filters) && $configFilters)
		{
			//iterate over the filters and create field objects for them
			foreach ($configFilters as $name => $filter)
			{
				if ($fieldObject = $this->make($name, $filter))
				{
					//the filters array is indexed on the field name and holds the arrayed values for the filters
					$this->filters[$fieldObject->getOption('field_name')] = $fieldObject;
				}
			}
		}

		return $this->filters;
	}

	/**
	 * Gets the filters array and converts the objects to arrays
	 *
	 * @return array
	 */
	public function getFiltersArrays()
	{
		if (!sizeof($this->filtersArrays))
		{
			foreach ($this->getFilters() as $name => $filter)
			{
				$this->filtersArrays[$name] = $filter->getOptions();
			}
		}

		return $this->filtersArrays;
	}

	/**
	 * Finds a field's options given a field name and a type (filter/edit)
	 *
	 * @param  string 		$field
	 * @param  string 		$type
	 *
	 * @return mixed
	 */
	public function getFieldObjectByName($field, $type)
	{
		$info = false;

		//we want to get the correct options depending on the type of field it is
		if ($type === 'filter')
		{
			$fields = $this->getFilters();
		}
		else
		{
			$fields = $this->getEditFields();
		}

		//iterate over the fields to get the one for this $field value
		foreach ($fields as $key => $val)
		{
			if ($key === $field)
			{
				$info = $val;
			}
		}

		return $info;
	}

	/**
	 * Given a model, field, type (filter or edit), and constraints (either int or array), returns an array of options
	 *
	 * @param string	$field
	 * @param string	$type			//either 'filter' or 'edit'
	 * @param array		$constraints	//an array of ids of the other model's items
	 * @param array		$selectedItems	//an array of ids that are currently selected
	 * @param string	$term			//the search term
	 *
	 * @return array
	 */
	public function updateRelationshipOptions($field, $type, $constraints, $selectedItems, $term = null)
	{
		//first get the related model and fetch the field's options
		$model = $this->config->getDataModel();
		$relatedModel = $model->{$field}()->getRelated();
		$relatedTable = $relatedModel->getTable();
		$relatedKeyName = $relatedModel->getKeyName();
		$relatedKeyTable = $relatedTable . '.' . $relatedKeyName;
		$fieldObject = $this->getFieldObjectByName($field, $type);

		//if we can't find the field, return an empty array
		if (!$fieldObject)
		{
			return array();
		}

		//make sure we're grouping by the model's id
		$query = $relatedModel->newQuery();

		//set up the selects
		$query->select(array($this->db->raw($this->db->getTablePrefix() . $relatedTable.'.*')));

		//format the selected items into an array
		$selectedItems = $this->formatSelectedItems($selectedItems);

		//if this is an autocomplete field, check if there is a search term. If not, just return the selected items
		if ($fieldObject->getOption('autocomplete') && !$term)
		{
			if (sizeof($selectedItems))
			{
				$this->filterQueryBySelectedItems($query, $selectedItems, $fieldObject, $relatedKeyTable);

				return $this->formatSelectOptions($fieldObject, $query->get());
			}
			else
			{
				return array();
			}
		}

		//applies constraints if there are any
		$this->applyConstraints($constraints, $query, $fieldObject);

		//if there is a search term, limit the result set by that term
		$this->filterBySearchTerm($term, $query, $fieldObject, $selectedItems, $relatedKeyTable);

		//perform any user-supplied options filter
		$filter = $fieldObject->getOption('options_filter');
		$filter($query);

		//finally we can return the options
		return $this->formatSelectOptions($fieldObject, $query->get());
	}

	/**
	 * Filters a relationship options query by a search term
	 *
	 * @param mixed										$term
	 * @param \Illuminate\Database\Query\Builder		$query
	 * @param array										$selectedItems
	 * @param \Frozennode\Administrator\Fields\Field	$fieldObject
	 * @param string									$relatedKeyTable
	 */
	public function filterBySearchTerm($term, EloquentBuilder &$query, Field $fieldObject, array $selectedItems, $relatedKeyTable)
	{
		if ($term)
		{
			//set up the wheres
			foreach ($fieldObject->getOption('search_fields') as $search)
			{
				$query->where($this->db->raw($search), 'LIKE', '%'.$term.'%');
			}

			//exclude the currently-selected items if there are any
			if (count($selectedItems))
			{
				$query->whereNotIn($relatedKeyTable, $selectedItems);
			}

			//set up the limits
			$query->take($fieldObject->getOption('num_options') + count($selectedItems));
		}
	}

	/**
	 * Takes the supplied $selectedItems mixed value and formats it to a usable array
	 *
	 * @param mixed		$selectedItems
	 *
	 * @return array
	 */
	public function formatSelectedItems($selectedItems)
	{
		if ($selectedItems)
		{
			//if this isn't an array, set it up as one
			return is_array($selectedItems) ? $selectedItems : explode(',', $selectedItems);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Takes the supplied $selectedItems mixed value and formats it to a usable array
	 *
	 * @param \Illuminate\Database\Query\Builder		$query
	 * @param array										$selectedItems
	 * @param \Frozennode\Administrator\Fields\Field	$fieldObject
	 * @param string									$relatedKeyTable
	 *
	 * @return array
	 */
	public function filterQueryBySelectedItems(EloquentBuilder &$query, array $selectedItems, Field $fieldObject, $relatedKeyTable)
	{
		$query->whereIn($relatedKeyTable, $selectedItems);

		//if this is a BelongsToMany and a sort field is set, order it by the sort field
		if ($fieldObject->getOption('multiple_values') && $fieldObject->getOption('sort_field'))
		{
			$query->orderBy($fieldObject->getOption('sort_field'));
		}
		//otherwise order it by the name field
		else
		{
			$query->orderBy($fieldObject->getOption('name_field'));
		}
	}

	/**
	 * Takes the supplied $selectedItems mixed value and formats it to a usable array
	 *
	 * @param mixed										$constraints
	 * @param \Illuminate\Database\Query\Builder		$query
	 * @param \Frozennode\Administrator\Fields\Field	$fieldObject
	 *
	 * @return array
	 */
	public function applyConstraints($constraints, EloquentBuilder &$query, Field $fieldObject)
	{
		$configConstraints = $fieldObject->getOption('constraints');

		if (sizeof($configConstraints))
		{
			//iterate over the config constraints
			foreach ($configConstraints as $key => $relationshipName)
			{
				//now that we're looping through the constraints, check to see if this one was supplied
				if (isset($constraints[$key]) && $constraints[$key] && sizeof($constraints[$key]))
				{
					//first we get the other model and the relationship field on it
					$model = $this->config->getDataModel();
					$relatedModel = $model->{$fieldObject->getOption('field_name')}()->getRelated();
					$otherModel = $model->{$key}()->getRelated();

					//set the data model for the config
					$this->config->setDataModel($otherModel);
					$otherField = $this->make($relationshipName, array('type' => 'relationship'), false);

					//constrain the query
					$otherField->constrainQuery($query, $relatedModel, $constraints[$key]);

					//set the data model back to the original
					$this->config->setDataModel($model);
				}
			}
		}
	}

	/**
	 * Takes an eloquent result array and turns it into an options array that can be used in the UI
	 *
	 * @param \Frozennode\Administrator\Fields\Field	$field
	 * @param \Illuminate\Database\Eloquent\Collection	$results
	 *
	 * @return array
	 */
	public function formatSelectOptions(Field $field, EloquentCollection $results)
	{
		$return = array();

		foreach ($results as $m)
		{
			$return[] = array(
				'id' => $m->getKey(),
				'text' => strval($m->{$field->getOption('name_field')}),
			);
		}

		return $return;
	}
}
