<?php
namespace Admin\Libraries\Fields;

class Enum extends Field {

	/**
	 * The options used for the enum field
	 *
	 * @var array
	 */
	public $options = array();

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

		$this->options = array_get($info, 'options', $this->options);
		$this->value = $this->value === '' ? null : $this->value;
	}

	/**
	 * Fill a model with input data
	 *
	 * @param Eloquent	$model
	 * @param mixed		$input
	 */
	public function fillModel(&$model, $input)
	{
		$model->{$this->field} = $input;
	}

	/**
	 * Filters a query object
	 *
	 * @param Query		$query
	 * @param Eloquent	$model
	 *
	 * @return void
	 */
	public function filterQuery(&$query, $model)
	{
		//run the parent method
		parent::filterQuery($query, $model);

		//if there is no value, return
		if (!$this->value)
		{
			return;
		}

		$query->where($model->table().'.'.$this->field, '=', $this->value);
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['options'] = $this->options;

		return $arr;
	}
}