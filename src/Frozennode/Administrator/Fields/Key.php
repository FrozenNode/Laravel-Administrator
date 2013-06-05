<?php
namespace Frozennode\Administrator\Fields;

class Key extends Field {

	/**
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array			$info
	 * @param ModelConfig 	$config
	 */
	public function __construct($field, $info, $config)
	{
		parent::__construct($field, $info, $config);

		$this->visible = array_get($info, 'visible', $this->visible);
		$this->editable = false;
	}

	/**
	 * Filters a query object given
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

		$query->where($model->getTable().'.'.$this->field, '=', $this->value);
	}
}