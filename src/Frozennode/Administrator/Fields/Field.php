<?php
namespace Frozennode\Administrator\Fields;

use Illuminate\Database\Eloquent\Model;

abstract class Field {

	/**
	 * The valid field types and their associated classes
	 *
	 * @var array
	 */
	protected static $fieldTypes = array(
		'key' => 'Frozennode\\Administrator\\Fields\\Key',
		'text' => 'Frozennode\\Administrator\\Fields\\Text',
		'textarea' => 'Frozennode\\Administrator\\Fields\\Text',
		'wysiwyg' => 'Frozennode\\Administrator\\Fields\\Text',
		'markdown' => 'Frozennode\\Administrator\\Fields\\Text',
		'date' => 'Frozennode\\Administrator\\Fields\\Time',
		'time' => 'Frozennode\\Administrator\\Fields\\Time',
		'datetime' => 'Frozennode\\Administrator\\Fields\\Time',
		'number' => 'Frozennode\\Administrator\\Fields\\Number',
		'bool' => 'Frozennode\\Administrator\\Fields\\Bool',
		'enum' => 'Frozennode\\Administrator\\Fields\\Enum',
		'image' => 'Frozennode\\Administrator\\Fields\\Image',
		//'multi_image' => 'Frozennode\\Administrator\\Fields\\MultiImage',
		'file' => 'Frozennode\\Administrator\\Fields\\File',
		'color' => 'Frozennode\\Administrator\\Fields\\Color',

		//relationships
		'belongs_to' => 'Frozennode\\Administrator\\Fields\\Relationships\\BelongsTo',
		'has_one' => 'Frozennode\\Administrator\\Fields\\Relationships\\HasOne',
		'has_many' => 'Frozennode\\Administrator\\Fields\\Relationships\\HasMany',
		'has_many_and_belongs_to' => 'Frozennode\\Administrator\\Fields\\Relationships\\HasManyAndBelongsTo',

	);

	/**
	 * List of possible related object class names
	 */
	static $relationshipBase = 'Illuminate\\Database\\Eloquent\\Relations\\';

	/**
	 * This is used in setting up filters
	 *
	 * @var bool
	 */
	public $relationship = false;

	/**
	 * If this is true, the field is an external field (i.e. it's a relationship but not a belongs_to)
	 *
	 * @var bool
	 */
	public $external = false;

	/**
	 * If this is true, the field is editable
	 *
	 * @var bool
	 */
	public $editable = true;

	/**
	 * Determines if the field is visible
	 *
	 * @var bool
	 */
	public $visible = true;

	/**
	 * The name of the field
	 *
	 * @var string
	 */
	public $field = '';

	/**
	 * The field type which matches a $fieldTypes key
	 *
	 * @var string
	 */
	public $type = false;

	/**
	 * When a field is instantiated, it is give its field type which matches a $fieldTypes key
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * The value (used in filter)
	 *
	 * @var string
	 */
	public $value = '';

	/**
	 * The minimum value (used in range filter)
	 *
	 * @var string
	 */
	public $minValue = '';

	/**
	 * The maximum value (used in range filter)
	 *
	 * @var string
	 */
	public $maxValue = '';

	/**
	 * Determines if a type has a min/max range
	 *
	 * @var string
	 */
	public $minMax = false;


	/**
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param ModelConfig 	$config
	 */
	public function __construct($field, $info, $config)
	{
		$this->type = $info['type'];
		$this->title = array_get($info, 'title', $field);
		$this->editable = array_get($info, 'editable', $this->editable);
		$this->value = static::getFilterValue(array_get($info, 'value', $this->value));
		$this->minValue = static::getFilterValue(array_get($info, 'minValue', $this->minValue));
		$this->maxValue = static::getFilterValue(array_get($info, 'maxValue', $this->maxValue));
		$this->field = $field;
	}


	/**
	 * Takes a the key/value of the options array and the associated model and returns an instance of the field
	 *
	 * @param string|int			$field 				//the key of the options array
	 * @param array|string			$info 				//the value of the options array
	 * @param ModelConfig|Eloquent	$config				//the model or settings config or an eloquent object (for relationships)
	 * @param bool	 				$loadRelationships	//determines whether or not to load the relationships
	 *
	 * @return false|Field object
	 */
	public static function get($field, $info, $config, $loadRelationships = true)
	{
		//put the model in a variable so we can call it statically
		$isModel = is_a($config, 'Frozennode\\Administrator\\ModelConfig');
		$isSettings = is_a($config, 'Frozennode\\Administrator\\SettingsConfig');
		$model = $isModel ? $config->model : $config;
		$noInfo = is_numeric($field);

		$field = $noInfo ? $info : $field;
		$info = $noInfo ? array() : $info;

		//first we check if the field is the same as the primary key. if so it will be a key type
		if ($isModel && $field === $model->getKeyName())
		{
			$info['type'] = 'key';
		}

		//get the type key from the info array if it exists
		$info['type'] = array_get($info, 'type', 'text');

		//the key field isn't allowed in the settings config
		if ($isSettings && $info['type'] === 'key')
		{
			return false;
		}

		//if this is a relationship, get the right key based on the supplied model
		if ($info['type'] === 'relationship')
		{
			if ($isSettings)
			{
				return false;
			}
			else if ($relationshipKey = static::getRelationshipKey($field, $model))
			{
				$info['type'] = $relationshipKey;
			}
			else
			{
				return false;
			}

			//if we should load the relationships, set the $info key
			if ($loadRelationships && !array_get($info, 'autocomplete', false))
			{
				$info['load_relationships'] = true;
			}
		}

		//now we check if the remaining type is valid
		if (!static::typeCheck($info['type'], $isSettings))
		{
			return false;
		}

		//now we can instantiate the object
		$classname = static::$fieldTypes[$info['type']];

		return new $classname($field, $info, $config);
	}

	/**
	 * Check to see if the type is valid
	 *
	 * @param string 	$type  			the field type to check
	 *
	 * @return bool
	 */
	protected static function typeCheck($type = '')
	{
		//if an improper value was supplied
		if (!$type || !is_string($type))
		{
			return false;
		}

		return array_key_exists($type, static::$fieldTypes);
	}

	/**
	 * Given a field name and Eloquent model object, returns the type key or false
	 *
	 * @param string 	$field  	the field type to check
	 * @param Eloquent	$model
	 *
	 * @return string|false
	 */
	protected static function getRelationshipKey($field, $model)
	{
		//check if the related method exists on the model
		if (!method_exists($model, $field))
		{
			return false;
		}

		//now that we know the method exists, we can determine if it's multiple or single
		$related_model = $model->{$field}();

		//check if this is a valid relationship object, and return the appropriate key
		if (is_a($related_model, static::$relationshipBase.'BelongsTo'))
		{
			return 'belongs_to';
		}
		else if (is_a($related_model, static::$relationshipBase.'HasOne'))
		{
			return 'has_one';
		}
		else if (is_a($related_model, static::$relationshipBase.'HasMany'))
		{
			return 'has_many';
		}
		else if (is_a($related_model, static::$relationshipBase.'BelongsToMany'))
		{
			return 'has_many_and_belongs_to';
		}
		else
		{
			return false;
		}
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'type' => $this->type,
			'field' => $this->field,
			'title' => $this->title,
			'editable' => $this->editable,
			'visible' => $this->visible,
			'value' => $this->value,
			'minMax' => $this->minMax,
			'minValue' => $this->minValue,
			'maxValue' => $this->maxValue,
			'editable' => $this->editable,
			'relationship' => $this->relationship,
		);
	}

	/**
	 * Fill a model with input data
	 *
	 * @param Eloquent	$model
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input)
	{
		$model->{$this->field} = is_null($input) ? '' : $input;
	}

	/**
	 * Filters a query object given
	 *
	 * @param Query		$query
	 * @param Eloquent	$model
	 *
	 * @return void
	 */
	public function filterQuery(&$query, $model)
	{
		//if this field has a min/max range, set it
		if ($this->minMax)
		{
			if ($this->minValue)
			{
				$query->where($model->getTable().'.'.$this->field, '>=', $this->minValue);
			}

			if ($this->maxValue)
			{
				$query->where($model->getTable().'.'.$this->field, '<=', $this->maxValue);
			}
		}
	}

	/**
	 * Helper function to determine if a filter value should be considered "empty" or not
	 *
	 * @param string 	value
	 *
	 * @return false|string
	 */
	private static function getFilterValue($value)
	{
		if (empty($value) || (is_string($value) && trim($value) === ''))
		{
			return false;
		}
		else
		{
			return $value;
		}
	}

	/**
	 * Given a model config and a field name, this returns the field object
	 *
	 * @param ModelConfig 	$config
	 * @param string	 	$field
	 *
	 * @return false|Field object
	 */
	public static function findField($config, $field)
	{
		$fields = static::getEditFields($config, false);

		//iterate over the object fields until we have our match
		return isset($fields['objectFields'][$field]) ? $fields['objectFields'][$field] : false;
	}

	/**
	 * Gets the formatted edit fields
	 *
	 * @param ModelConfig|SettingsConfig	$config
	 * @param bool							$loadRelationships
	 *
	 * @return array
	 */
	public static function getEditFields($config, $loadRelationships = true)
	{
		$isModel = is_a($config, 'Frozennode\\Administrator\\ModelConfig');
		$isSettings = is_a($config, 'Frozennode\\Administrator\\SettingsConfig');

		//put the model into a variable so we can call it statically
		if ($isModel)
		{
			$model = $config->model;
		}

		//this is the return value
		$return = array(
			'objectFields' => array(),
			'arrayFields' => array(),
			'dataModel' => array(),
		);

		//iterate over each supplied edit field
		foreach ($config->edit as $field => $info)
		{
			$fieldObject = static::get($field, $info, $config, $loadRelationships);

			//if this field can be properly set up, put it into the edit fields array
			if ($fieldObject)
			{
				$return['objectFields'][$fieldObject->field] = $fieldObject;
				$return['arrayFields'][$fieldObject->field] = $fieldObject->toArray();
			}
		}

		//add the id field, which will be uneditable, but part of the data model
		if (!$isSettings && !isset($return['arrayFields'][$model->getKeyName()]))
		{
			$keyField = static::get($model->getKeyName(), array('visible' => false), $config);

			if ($keyField)
			{
				$return['arrayFields'][$model->getKeyName()] = $keyField->toArray();
			}
			else
			{
				$return['arrayFields'][$model->getKeyName()] = 0;
			}
		}

		//set up the data model
		if (!$isSettings)
		{
			foreach ($return['arrayFields'] as $field => $info)
			{
				//if this is a key, set it to 0
				if ($info['type'] === 'key')
				{
					$return['dataModel'][$field] = 0;
				}
				else if (is_array($info) || is_a($info, 'Field'))
				{
					//if this is a collection, convert it to an array
					if (is_a($model->$field, 'Illuminate\Database\Eloquent\Collection'))
					{
						$return['dataModel'][$field] = $model->$field->toArray();
					}
					else
					{
						$return['dataModel'][$field] = $model->$field;
					}
				}
				else
				{
					$return['dataModel'][$field] = $info;
				}
			}
		}

		return $return;
	}

	/**
	 * Gets the filters for the given model config
	 *
	 * @param ModelConfig	$config
	 *
	 * @return array
	 */
	public static function getFilters($config)
	{
		//get the model's filter fields
		$filters = array();

		//if there are no filters, exit out early
		if ($config->filters)
		{
			//iterate over the filters and create field objects for them
			foreach ($config->filters as $field => $info)
			{
				if ($fieldObject = Field::get($field, $info, $config))
				{
					$filters[$fieldObject->field] = $fieldObject->toArray();
				}
			}
		}


		return $filters;
	}

	/**
	 * Finds a field's options given a field name, a model, and a type (filter/edit)
	 *
	 * @param  string 		$field
	 * @param  ModelConfig	$config
	 * @param  string 		$type
	 *
	 * @return array|false
	 */
	public static function getOptions($field, $config, $type)
	{
		$info = false;

		//we want to get the correct options depending on the type of field it is
		if ($type === 'filter')
		{
			$fields = static::getFilters($config);
		}
		else
		{
			$editFields = static::getEditFields($config);
			$fields = $editFields['arrayFields'];
		}

		//iterate over the fields to get the one for this $field value
		foreach ($fields as $key => $val)
		{
			if ($key === $field)
			{
				$info = $val;
			}
		}

		//if we can't find the field, return an empty array
		return $info;
	}
}