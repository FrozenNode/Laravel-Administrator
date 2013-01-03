<?php
namespace Admin\Libraries\Fields;

class Key extends Field {


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

		$query->where($model->table().'.'.$this->field, '=', $this->value);
	}
}