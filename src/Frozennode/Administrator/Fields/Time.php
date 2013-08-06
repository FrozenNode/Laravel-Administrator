<?php
namespace Frozennode\Administrator\Fields;

use Illuminate\Database\Query\Builder as QueryBuilder;

class Time extends Field {

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array(
		'min_max' => true,
		'date_format' => 'yy-mm-dd',
		'time_format' => 'HH:mm',
	);

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = array(
		'date_format' => 'string',
		'time_format' => 'string',
	);

	/**
	 * Filters a query object
	 *
	 * @param \Illuminate\Database\Query\Builder	$query
	 * @param array									$selects
	 *
	 * @return void
	 */
	public function filterQuery(QueryBuilder &$query, &$selects = null)
	{
		$model = $this->config->getDataModel();

		//try to read the time for the min and max values, and if they check out, set the where
		if ($minValue = $this->getOption('min_value'))
		{
			$time = strtotime($minValue);

			if ($time !== false)
			{
				$query->where($model->getTable().'.'.$this->getOption('field_name'), '>=', $this->getDateString($time));
			}
		}

		if ($maxValue = $this->getOption('max_value'))
		{
			$time = strtotime($maxValue);

			if ($time !== false)
			{
				$query->where($model->getTable().'.'.$this->getOption('field_name'), '<=', $this->getDateString($time));
			}
		}
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
		$val = ( is_null($input) || !is_string($input) ) ? '' : $input;
		$time = strtotime($val);

		//first we validate that it's a date/time
		if ($time !== false)
		{
			//fill the model with the correct date/time format
			$model->{$this->getOption('field_name')} = $this->getDateString($time);
		}
	}

	/**
	 * Get a date format from a time depending on the type of time field this is
	 *
	 * @param int		$time
	 *
	 * @return string
	 */
	public function getDateString($time)
	{
		if ($this->getOption('type') === 'date')
		{
			return date('Y-m-d', $time);
		}
		else if ($this->getOption('type') === 'datetime')
		{
			return date('Y-m-d H:i:s', $time);
		}
		else
		{
			return date('H:i:s', $time);
		}
	}
}