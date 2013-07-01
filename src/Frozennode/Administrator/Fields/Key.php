<?php
namespace Frozennode\Administrator\Fields;

class Key extends Field {

	/**
	 * If this is true, the field is editable
	 *
	 * @var bool
	 */
	public $editable = false;

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
		//run the parent method
		parent::filterQuery($query, $model, $selects);

		//if there is no value, return
		if (!$this->value)
		{
			return;
		}

		$query->where($model->getTable().'.'.$this->field, '=', $this->value);
	}
}