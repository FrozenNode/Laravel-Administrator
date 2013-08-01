<?php
namespace Frozennode\Administrator\Fields;

class Key extends Field {

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array(
		'editable' => false,
	);

	/**
	 * Fill a model with input data
	 *
	 * @param Eloquent	$model
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input) {}

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
		if (!$this->getOption('value'))
		{
			return;
		}

		$query->where($this->config->getDataModel()->getTable().'.'.$this->getOption('field_name'), '=', $this->getOption('value'));
	}
}