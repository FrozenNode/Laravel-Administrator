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
		'markdown' => 'Admin\\Libraries\\Fields\\Text',
		'date' => 'Admin\\Libraries\\Fields\\Time',
		'time' => 'Admin\\Libraries\\Fields\\Time',
		'datetime' => 'Admin\\Libraries\\Fields\\Time',
		'number' => 'Admin\\Libraries\\Fields\\Number',
		'bool' => 'Admin\\Libraries\\Fields\\Bool',
		'enum' => 'Admin\\Libraries\\Fields\\Enum',
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
	 * If this is true, the field is editable
	 *
	 * @var bool
	 */
	public $editable = true;

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
		//$this->editable = array_get($info, 'editable', $this->editable);
		$this->value = static::getFilterValue(array_get($info, 'value', $this->value));
		$this->minValue = static::getFilterValue(array_get($info, 'minValue', $this->minValue));
		$this->maxValue = static::getFilterValue(array_get($info, 'maxValue', $this->maxValue));
		$this->field = $field;
	}


	/**
	 * Takes a the key/value of the options array and the associated model and returns an instance of the field
	 *
	 * @param string|int	$field 				//the key of the options array
	 * @param array|string	$info 				//the value of the options array
	 * @param Eloquent 		$model 				//an instance of the Eloquent model
	 * @param bool	 		$loadRelationships	//determines whether or not to load the relationships
	 *
	 * @return false|Field object
	 */
	public static function get($field, $info, $model, $loadRelationships = true)
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

			//if we should load the relationships, set the $info key
			if ($loadRelationships && !array_get($info, 'autocomplete', false))
			{
				$info['load_relationships'] = true;
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
	 * @param bool		$loadRelationships
	 *
	 * @return array
	 */
	public static function getEditFields($model, $loadRelationships = true)
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
				$fieldObject = static::get($field, $info, $model, $loadRelationships);

				//if this field can be properly set up, put it into the edit fields array
				if ($fieldObject)
				{
					$return['objectFields'][$fieldObject->field] = $fieldObject;
					$return['arrayFields'][$fieldObject->field] = $fieldObject->toArray();
				}
			}
		}

		//add the id field, which will be uneditable, but part of the data model
		$return['arrayFields'][$model::$key] = 0;

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

	/**
	 * Gets the filters for the given model
	 *
	 * @param object	$model
	 *
	 * @return array
	 */
	public static function getFilters($model)
	{
		//get the model's edit fields
		$filters = array();

		//if the filters option is set, use it
		if (isset($model->filters) && count($model->filters) > 0)
		{
			foreach ($model->filters as $field => $info)
			{
				if ($fieldObject = Field::get($field, $info, $model))
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
	 * @param  string 	$field
	 * @param  Eloquent $model
	 * @param  string 	$type
	 *
	 * @return array|false
	 */
	public static function getOptions($field, $model, $type)
	{
		$info = false;

		//we want to get the correct options depending on the type of field it is
		if ($type === 'filter')
		{
			$fields = static::getFilters($model);
		}
		else
		{
			$editFields = static::getEditFields($model);
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