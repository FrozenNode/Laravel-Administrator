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

		$this->value = $this->value === '' ? null : $this->value;
		$options = array_get($info, 'options', $this->options);

		//iterate over the options to create the options assoc array
		foreach ($options as $val => $text)
		{
			$this->options[] = array(
				'text' => $text,
				'value' => is_numeric($val) ? $text : $val,
			);
		}
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