<?php
namespace Admin\Libraries\Fields;

use \DateTime;

class Time extends Field {

	/**
	 * Determines whether this field's filter uses a min/max range
	 *
	 * @var string
	 */
	public $minMax = true;

	/**
	 * Format string for the jQuery UI Datepicker
	 * http://docs.jquery.com/UI/Datepicker/formatDate
	 *
	 * @var string
	 */
	public $date_format = 'yy-mm-dd';

	/**
	 * Format string for the jQUery timepicker plugin
	 * http://trentrichardson.com/examples/timepicker/#tp-formatting
	 *
	 * @var string
	 */
	public $time_format = 'HH:mm';


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

		$this->date_format = array_get($info, 'date_format', $this->date_format);
		$this->time_format = array_get($info, 'time_format', $this->time_format);
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['date_format'] = $this->date_format;
		$arr['time_format'] = $this->time_format;

		return $arr;
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
		$val = ( is_null($input) || !is_string($input) ) ? '' : $input;

		if (strtotime($val) !== false)
		{
			$model->{$this->field} = new DateTime($val);
		}
	}
}