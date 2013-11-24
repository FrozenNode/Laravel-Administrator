<?php
namespace Frozennode\Administrator\Fields;

use Illuminate\Database\Query\Builder as QueryBuilder;

class Bool extends Field {

	/**
	 * The value (used in filter)
	 *
	 * @var bool
	 */
	public $value = false;

	/**
	 * Builds a few basic options
	 *
	 * @return void
	 */
	public function build()
	{
		parent::build();

		$value = $this->validator->arrayGet($this->suppliedOptions, 'value', true);

		//we need to set the value to 'false' when it is falsey so it plays nicely with select2
		if (!$value && $value !== '')
		{
			$this->suppliedOptions['value'] = 'false';
		}
	}

	/**
	 * Fill a model with input data
	 *
	 * @param \Illuminate\Database\Eloquent\Model	$model
	 * @param mixed									$input
	 */
	public function fillModel(&$model, $input)
	{
		$model->{$this->getOption('field_name')} = $input === 'true' || $input === '1' ? 1 : 0;
	}

	/**
	 * Sets the filter options for this item
	 *
	 * @param array		$filter
	 *
	 * @return void
	 */
	public function setFilter($filter)
	{
		parent::setFilter($filter);

		$this->userOptions['value'] = $this->validator->arrayGet($filter, 'value', '');

		//if it isn't null, we have to check the 'true'/'false' string
		if ($this->userOptions['value'] !== '')
		{
			$this->userOptions['value'] = $this->userOptions['value'] === 'false' || !$this->userOptions['value'] ? 0 : 1;
		}
	}

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
		//if the field isn't empty
		if ($this->getOption('value') !== '')
		{
			$query->where($this->config->getDataModel()->getTable().'.'.$this->getOption('field_name'), '=', $this->getOption('value'));
		}
	}
}
