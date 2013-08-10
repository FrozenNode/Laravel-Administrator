<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Database\Query\Builder as QueryBuilder;

abstract class Field {

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
	 * The originally supplied options
	 *
	 * @var array
	 */
	protected $suppliedOptions;

	/**
	 * The options supplied merged into the defaults
	 *
	 * @var array
	 */
	protected $userOptions;

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
	 * @param \Frozennode\Administrator\Validator 				$validator
	 * @param \Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param \Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		$this->validator = $validator;
		$this->config = $config;
		$this->db = $db;
		$this->suppliedOptions = $options;
	}

	/**
	 * Builds a few basic options
	 *
	 * @return void
	 */
	public function build()
	{
		$options = $this->suppliedOptions;

		//set the title if it doesn't exist
		$options['title'] = $this->validator->arrayGet($options, 'title', $options['field_name']);

		//run the visible property closure if supplied
		$visible = $this->validator->arrayGet($options, 'visible');

		if (is_callable($visible))
		{
			$options['visible'] = $visible($this->config->getDataModel()) ? true : false;
		}

		//run the editable property's closure if supplied
		$editable = $this->validator->arrayGet($options, 'editable');

		if (isset($editable) && is_callable($editable))
		{
			$options['editable'] = $editable($this->config->getDataModel());
		}

		$this->suppliedOptions = $options;
	}

	/**
	 * Validates the supplied options
	 *
	 * @return void
	 */
	public function validateOptions()
	{
		//override the config
		$this->validator->override($this->suppliedOptions, $this->getRules());

		//if the validator failed, throw an exception
		if ($this->validator->fails())
		{
			throw new \InvalidArgumentException("There are problems with your '" . $this->suppliedOptions['field_name'] . "' field in the " .
									$this->config->getOption('name') . " config: " .	implode('. ', $this->validator->messages()->all()));
		}
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
	 * @param \Illuminate\Database\Eloquent\Model	$model
	 * @param mixed									$input
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
		$this->userOptions['min_value'] = $this->getFilterValue($this->validator->arrayGet($filter, 'min_value', $this->getOption('min_value')));
		$this->userOptions['max_value'] = $this->getFilterValue($this->validator->arrayGet($filter, 'max_value', $this->getOption('max_value')));
	}

	/**
	 * Filters a query object given
	 *
	 * @param \Illuminate\Database\Query\Builder	$query
	 * @param array									$selects
	 *
	 * @return void
	 */
	public function filterQuery(QueryBuilder &$query, &$selects = null)
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
	public function getFilterValue($value)
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
		if (empty($this->userOptions))
		{
			//validate the options and then merge them into the defaults
			$this->build();
			$this->validateOptions();
			$this->userOptions = array_merge($this->getDefaults(), $this->suppliedOptions);
		}

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
			throw new \InvalidArgumentException("An invalid option '$key' was searched for in the '" . $this->userOptions['field_name'] . "' field");
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