<?php
namespace Admin\Libraries\Fields;

use \Eloquent;

abstract class Field {

	/**
	 * The valid field types and their associated classes
	 *
	 * @var array
	 */
	protected static $fieldTypes = array(
		'key' => 'Admin\\Libraries\\Fields\\Key',
		'text' => 'Admin\\Libraries\\Fields\\Text',
		'textarea' => 'Admin\\Libraries\\Fields\\Text',
		'wysiwyg' => 'Admin\\Libraries\\Fields\\Text',
		'date' => 'Admin\\Libraries\\Fields\\Time',
		'time' => 'Admin\\Libraries\\Fields\\Time',
		'datetime' => 'Admin\\Libraries\\Fields\\Time',
		'number' => 'Admin\\Libraries\\Fields\\Number',
		'image' => 'Admin\\Libraries\\Fields\\Image',
		'multi_image' => 'Admin\\Libraries\\Fields\\MultiImage',
		'file' => 'Admin\\Libraries\\Fields\\File',

		//relationships
		'belongs_to' => 'Admin\\Libraries\\Fields\\Relationships\\BelongsTo',
		'has_one' => 'Admin\\Libraries\\Fields\\Relationships\\HasOne',
		'has_many' => 'Admin\\Libraries\\Fields\\Relationships\\HasMany',
		'has_many_and_belongs_to' => 'Admin\\Libraries\\Fields\\Relationships\\HasManyAndBelongsTo',

	);

	/**
	 * List of possible related object class names
	 */
	static $relationshipBase = 'Laravel\\Database\\Eloquent\\Relationships\\';

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
	 * @param Eloquent 		$model
	 */
	public function __construct($field, $info, $model)
	{
		$this->type = $info['type'];
		$this->title = array_get($info, 'title', $field);
		$this->value = static::getFilterValue(array_get($info, 'value', $this->value));
		$this->minValue = static::getFilterValue(array_get($info, 'minValue', $this->minValue));
		$this->maxValue = static::getFilterValue(array_get($info, 'maxValue', $this->maxValue));
		$this->field = $field;
	}


	/**
	 * Takes a the key/value of the options array and the associated model and returns an instance of the field
	 *
	 * @param string|int	$field 		//the key of the options array
	 * @param array|string	$info 		//the value of the options array
	 * @param Eloquent 		$model 		//an instance of the Eloquent model
	 *
	 * @return false|Field object
	 */
	public static function get($field, $info, $model)
	{
		$noInfo = is_numeric($field);

		$field = $noInfo ? $info : $field;
		$info = $noInfo ? array() : $info;

		//first we check if the field is the same as the primary key. if so it will be a key type
		if ($field === $model::$key)
		{
			$info['type'] = 'key';
		}

		//get the type key from the info array if it exists
		$info['type'] = array_get($info, 'type', 'text');

		//if this is a relationship, get the right key based on the supplied model
		if ($info['type'] === 'relationship')
		{
			if ($relationshipKey = static::getRelationshipKey($field, $model))
			{
				$info['type'] = $relationshipKey;
			}
			else
			{
				return false;
			}
		}

		//now we check if the remaining type is valid
		if (!static::typeCheck($info['type']))
		{
			return false;
		}

		//now we can instantiate the object
		$classname = static::$fieldTypes[$info['type']];

		return new $classname($field, $info, $model);
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
		if (is_a($related_model, static::$relationshipBase.'Belongs_To'))
		{
			return 'belongs_to';
		}
		else if (is_a($related_model, static::$relationshipBase.'Has_One'))
		{
			return 'has_one';
		}
		else if (is_a($related_model, static::$relationshipBase.'Has_Many'))
		{
			return 'has_many';
		}
		else if (is_a($related_model, static::$relationshipBase.'Has_Many_And_Belongs_To'))
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
			'value' => $this->value,
			'minValue' => $this->minValue,
			'maxValue' => $this->maxValue,
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
				$query->where($model->table().'.'.$this->field, '>=', $this->minValue);
			}

			if ($this->maxValue)
			{
				$query->where($model->table().'.'.$this->field, '<=', $this->maxValue);
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
	 * Gets the model's edit fields
	 *
	 * @param object	$model
	 *
	 * @return array
	 */
	public static function getEditFields($model)
	{
		$return = array(
			'objectFields' => array(),
			'arrayFields' => array(),
			'dataModel' => array(),
		);

		if (isset($model->edit) && count($model->edit) > 0)
		{
			foreach ($model->edit as $field => $info)
			{
				//if this field can be properly set up, put it into the edit fields array
				if ($fieldObject = static::get($field, $info, $model))
				{
					$return['objectFields'][$fieldObject->field] = $fieldObject;
					$return['arrayFields'][$fieldObject->field] = $fieldObject->toArray();
				}
			}
		}

		//add the id field, which will be uneditable, but part of the data model
		$return['arrayFields']['id'] = 0;

		//set up the data model
		foreach ($return['arrayFields'] as $field => $info)
		{
			if (is_array($info) || is_a($info, 'Field'))
			{
				$return['dataModel'][$field] = $model->$field;
			}
			else
			{
				$return['dataModel'][$field] = $info;
			}
		}

		return $return;
	}
}