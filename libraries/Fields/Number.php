<?php
namespace Admin\Libraries\Fields;

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
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param Eloquent 		$model
	 */
	public function __construct($field, $info, $model)
	{
		parent::__construct($field, $info, $model);

		$this->symbol = array_get($info, 'symbol', $this->symbol);
		$this->decimals = array_get($info, 'decimals', $this->decimals);
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

		return $arr;
	}
}