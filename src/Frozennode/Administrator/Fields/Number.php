<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class Number extends Field {

	/**
	 * Determines whether this field's filter uses a min/max range
	 *
	 * @var string
	 */
	public $minMax = true;

	/**
	 * The symbol to use in front of the number
	 *
	 * @var string
	 */
	public $symbol = '';

	/**
	 * The number of decimal places after the number
	 *
	 * @var int
	 */
	public $decimals = 0;

	/**
	 * The thousands separator
	 *
	 * @var string
	 */
	public $thousandsSeparator = ',';

	/**
	 * The decimal separator
	 *
	 * @var int
	 */
	public $decimalSeparator = '.';


	/**
	 * Create a new Number instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		parent::__construct($validator, $config, $db, $options);

		$this->symbol = $this->validator->arrayGet($options, 'symbol', $this->symbol);
		$this->decimals = $this->validator->arrayGet($options, 'decimals', $this->decimals);
		$this->decimalSeparator = $this->validator->arrayGet($options, 'decimal_separator', $this->decimalSeparator);
		$this->thousandsSeparator = $this->validator->arrayGet($options, 'thousands_separator', $this->thousandsSeparator);
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
		parent::setFilter($filter);

		$this->minValue = $this->minValue ? str_replace(',', '', $this->minValue) : $this->minValue;
		$this->maxValue = $this->maxValue ? str_replace(',', '', $this->maxValue) : $this->maxValue;
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
		$model->{$this->field} = is_null($input) || $input === '' ? null : $this->parseNumber($input);
	}

	/**
	 * Parses a user-supplied number into the required SQL format with no commas for thousands and a . for decimals
	 *
	 * @param string	$number
	 *
	 * @return string
	 */
	private function parseNumber($number)
	{
		return str_replace($this->decimalSeparator, '.', str_replace($this->thousandsSeparator, '', $number));
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['symbol'] = $this->symbol;
		$arr['decimals'] = $this->decimals;
		$arr['decimalSeparator'] = $this->decimalSeparator;
		$arr['thousandsSeparator'] = $this->thousandsSeparator;

		return $arr;
	}
}