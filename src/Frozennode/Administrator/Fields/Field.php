<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

abstract class Field {

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The config interface instance
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
	 * When a field is a setter, no value will be returned from the database and the value will be unset before saving
	 *
	 * @var bool
	 */
	public $setter = false;

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
	 * Create a new Field instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		$this->validator = $validator;
		$this->config = $config;
		$this->db = $db;

		//fill the basic fields
		$this->field = $options['field_name'];
		$this->type = $options['type'];
		$this->title = $validator->arrayGet($options, 'title', $this->field);
		$this->editable = $validator->arrayGet($options, 'editable', $this->editable);
		$this->setter = $validator->arrayGet($options, 'setter', $this->setter);
		$this->visible = $validator->arrayGet($options, 'visible', $this->visible);

		//make sure the hide callback is run if it's supplied
		if (is_callable($this->visible))
		{
			$visible = $this->visible;
			$this->visible = $visible($this->config->getDataModel()) ? true : false;
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
			'setter' => $this->setter,
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
	 * Sets the filter options for this item
	 *
	 * @param array		$filter
	 *
	 * @return void
	 */
	public function setFilter($filter)
	{
		$this->value = $this->getFilterValue($this->validator->arrayGet($filter, 'value', $this->value));
		$this->minValue = $this->getFilterValue($this->validator->arrayGet($filter, 'minValue', $this->minValue));
		$this->maxValue = $this->getFilterValue($this->validator->arrayGet($filter, 'maxValue', $this->maxValue));
	}

	/**
	 * Filters a query object given
	 *
	 * @param Illuminate\Database\Query\Builder		$query
	 * @param array									$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$query, &$selects = null)
	{
		$model = $this->config->getDataModel();

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

}