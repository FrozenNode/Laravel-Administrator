<?php
namespace Admin\Libraries\Fields;

class Bool extends Field {

	/**
	 * The value (used in filter)
	 *
	 * @var string
	 */
	public $value = false;

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

		$this->value = array_get($info, 'value', '');

		//if it isn't null, we have to check the 'true'/'false' string
		if ($this->value !== '')
		{
			$this->value = $this->value === 'true' ? 1 : 0;
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
		$model->{$this->field} = $input === 'true' ? 1 : 0;
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
		//if the field isn't empty
		if ($this->value !== '')
		{
			$query->where($model->table().'.'.$this->field, '=', $this->value);
		}
	}
}