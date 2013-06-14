<?php
namespace Frozennode\Administrator\Fields;

class Bool extends Field {

	/**
	 * The value (used in filter)
	 *
	 * @var bool
	 */
	public $value = false;

	/**
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param ModelConfig 	$config
	 */
	public function __construct($field, $info, $config)
	{
		parent::__construct($field, $info, $config);

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
		$model->{$this->field} = $input === 'true' || $input === '1' ? 1 : 0;
	}

	/**
	 * Filters a query object
	 *
	 * @param Query		$query
	 * @param Eloquent	$model
	 * @param array		$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$query, $model, &$selects)
	{
		//if the field isn't empty
		if ($this->value !== '')
		{
			$query->where($model->getTable().'.'.$this->field, '=', $this->value);
		}
	}
}