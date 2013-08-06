<?php
namespace Frozennode\Administrator\Fields;

class Number extends Field {

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array(
		'min_max' => true,
		'symbol' => '',
		'decimals' => 0,
		'thousands_separator' => ',',
		'decimal_separator' => '.',
	);

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = array(
		'symbol' => 'string',
		'decimals' => 'integer',
		'thousands_separator' => 'string',
		'decimal_separator' => 'string',
	);

	/**
	 * Sets the filter options for this item
	 *
	 * @param array		$filter
	 *
	 * @return void
	 */
	public function setFilter($filter)
	{
		parent::setFilter($filter);

		$minValue = $this->getOption('min_value');
		$maxValue = $this->getOption('max_value');

		$this->userOptions['min_value'] = $minValue ? str_replace(',', '', $minValue) : $minValue;
		$this->userOptions['max_value'] = $maxValue ? str_replace(',', '', $maxValue) : $maxValue;
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
		$model->{$this->getOption('field_name')} = is_null($input) || $input === '' ? null : $this->parseNumber($input);
	}

	/**
	 * Parses a user-supplied number into the required SQL format with no commas for thousands and a . for decimals
	 *
	 * @param string	$number
	 *
	 * @return string
	 */
	public function parseNumber($number)
	{
		return str_replace($this->getOption('decimal_separator'), '.', str_replace($this->getOption('thousands_separator'), '', $number));
	}
}