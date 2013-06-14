<?php
namespace Frozennode\Administrator\Fields;

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
	 * @param ModelConfig 	$config
	 */
	public function __construct($field, $info, $config)
	{
		parent::__construct($field, $info, $config);

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
	 * Filters a query object given
	 *
	 * @param Query		$query
	 * @param Eloquent	$model
	 * @param array		$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$query, $model, &$selects)
	{
		//try to read the time for the min and max values, and if they check out, set the where
		if ($this->minValue)
		{
			$time = strtotime($this->minValue);

			if ($time !== false)
			{
				$query->where($model->getTable().'.'.$this->field, '>=', $this->getDateString($time));
			}
		}

		if ($this->maxValue)
		{
			$time = strtotime($this->maxValue);

			if ($time !== false)
			{
				$query->where($model->getTable().'.'.$this->field, '<=', $this->getDateString($time));
			}
		}
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
		$time = strtotime($val);

		//first we validate that it's a date/time
		if ($time !== false)
		{
			//fill the model with the correct date/time format
			$model->{$this->field} = $this->getDateString($time);
		}
	}

	/**
	 * Get a date format from a time depending on the type of time field this is
	 *
	 * @param int		$time
	 *
	 * @return string
	 */
	protected function getDateString($time)
	{
		if ($this->type === 'date')
		{
			return date('Y-m-d', $time);
		}
		else if ($this->type === 'datetime')
		{
			return date('Y-m-d H:i:s', $time);
		}
		else
		{
			date('H:i:s', $time);
		}
	}
}