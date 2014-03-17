<?php
namespace Frozennode\Administrator\Field;

use Frozennode\Administrator\Config\ConfigInterface;
use Frozennode\Administrator\Traits\OptionableTrait;
use Illuminate\Database\Query\Builder as QueryBuilder;

abstract class Field {

	use OptionableTrait
	{
		getDefaultOptions as traitGetDefaultOptions;
		getRules as traitGetRules;
	}

	/**
	 * The config interface instance
	 *
	 * @var \Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $baseDefaultOptions = [
		'relationship' => false,
		'external' => false,
		'editable' => true,
		'visible' => true,
		'setter' => false,
		'value' => '',
		'min_value' => '',
		'max_value' => '',
		'min_max' => false,
	];

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaultOptions = [];

	/**
	 * The base rules that all fields need to pass
	 *
	 * @var array
	 */
	protected $baseRules = [
		'type' => 'required|string',
		'field_name' => 'required|string',
	];

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Create a new Field instance
	 *
	 * @param \Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param \Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(ConfigInterface $config, array $options)
	{
		$this->config = $config;
		$this->options = $options;
	}

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	abstract public function getConfigName();

	/**
	 * Builds a few basic options
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		//set the title if it doesn't exist
		$options['title'] = array_get($options, 'title', $options['field_name']);

		//run the visible property closure if supplied
		$visible = array_get($options, 'visible');

		if (is_callable($visible))
		{
			$options['visible'] = $visible($this->config->getDataModel()) ? true : false;
		}

		//run the editable property's closure if supplied
		$editable = array_get($options, 'editable');

		if (isset($editable) && is_callable($editable))
		{
			$options['editable'] = $editable($this->config->getDataModel());
		}

		return $options;
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
		$this->userOptions['value'] = $this->getFilterValue(array_get($filter, 'value', $this->getOption('value')));
		$this->userOptions['min_value'] = $this->getFilterValue(array_get($filter, 'min_value', $this->getOption('min_value')));
		$this->userOptions['max_value'] = $this->getFilterValue(array_get($filter, 'max_value', $this->getOption('max_value')));
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
	public function getDefaultOptions()
	{
		return array_merge($this->baseDefaultOptions, $this->defaultOptions);
	}

}