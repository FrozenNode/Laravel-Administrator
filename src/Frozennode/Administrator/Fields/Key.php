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
	 * Filters a query object
	 *
	 * @param Query		$query
	 * @param array		$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$query, &$selects = null)
	{
		//run the parent method
		parent::filterQuery($query, $selects);

		//if there is no value, return
		if (!$this->value)
		{
			return;
		}

		$query->where($this->config->getDataModel()->getTable().'.'.$this->field, '=', $this->value);
	}
}