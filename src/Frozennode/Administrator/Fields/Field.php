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
	 * The options supplied merged into the defaults
	 *
	 * @var bool
	 */
	public $userOptions;

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
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $baseDefaults = array(
		'relationship' => false,
		'external' => false,
		'editable' => true,
		'visible' => true,
		'setter' => false,
		'value' => '',
		'min_value' => '',
		'max_value' => '',
		'min_max' => false,
	);

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * The base rules that all fields need to pass
	 *
	 * @var array
	 */
	protected $baseRules = array(
		'type' => 'required|string',
		'field_name' => 'required|string',
	);

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = array();

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

		//set the title if it doesn't exist
		$options['title'] = $validator->arrayGet($options, 'title', $options['field_name']);

		//make sure the visible callback is run if it's supplied
		if (is_callable($validator->arrayGet($options, 'visible')))
		{
			$options['visible'] = $options['visible']($this->config->getDataModel()) ? true : false;
		}

		//override the config
		$validator->override($config, $this->getRules());

		//if the validator failed, throw an exception
		if ($validator->fails())
		{
			throw new \InvalidArgumentException("There are problems with your '" . $options['field_name'] . "' field: " .
												implode('. ', $validator->messages()->all()));
		}

		//fill up the instance with the user-supplied options
		$this->userOptions = array_merge($this->getDefaults(), $config);
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->getOptions();
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
		$model->{$this->getOption('field_name')} = is_null($input) ? '' : $input;
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
		$this->userOptions['value'] = $this->getFilterValue($this->validator->arrayGet($filter, 'value', $this->getOption('value')));
		$this->userOptions['min_value'] = $this->getFilterValue($this->validator->arrayGet($filter, 'minValue', $this->getOption('min_value')));
		$this->userOptions['max_value'] = $this->getFilterValue($this->validator->arrayGet($filter, 'maxValue', $this->getOption('max_value')));
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
		if ($this->getOption('min_max'))
		{
			if ($minValue = $this->getOption('min_value'))
			{
				$query->where($model->getTable().'.'.$this->getOption('field_name'), '>=', $minValue);
			}

			if ($maxValue = $this->getOption('max_value'))
			{
				$query->where($model->getTable().'.'.$this->getOption('field_name'), '<=', $maxValue);
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
	protected function getFilterValue($value)
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
	 * Gets all user options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->userOptions;
	}

	/**
	 * Gets a field's option
	 *
	 * @param string 	$key
	 *
	 * @return mixed
	 */
	public function getOption($key)
	{
		$options = $this->getOptions();

		if (!array_key_exists($key, $options))
		{
			throw new \InvalidArgumentException("An invalid option was searched for in the '" . $this->getOption('field_name') . "' field");
		}

		return $options[$key];
	}

	/**
	 * Gets all rules
	 *
	 * @return array
	 */
	public function getRules()
	{
		return array_merge($this->baseRules, $this->rules);
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		return array_merge($this->baseDefaults, $this->defaults);
	}

}