<?php namespace Frozennode\Administrator\Fields\Types;

use Frozennode\Administrator\Fields\Field;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Text extends Field {

	/**
	 * The default options for this field
	 *
	 * @var array
	 */
	protected $defaultOptions = [
		'limit' => 0,
		'height' => 100,
	];

	/**
	 * The rules for this field
	 *
	 * @var array
	 */
	protected $rules = [
		'limit' => 'integer|min:0',
		'height' => 'integer|min:0',
	];

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'text';
	}

	/**
	 * Filters a query object given
	 *
	 * @param \Illuminate\Database\Query\Builder	$query
	 * @param array									$selects
	 *
	 * @return void
	 */
	public function filterQuery(QueryBuilder &$query, &$selects = null)
	{
		//run the parent method
		parent::filterQuery($query, $selects);

		//if there is no value, return
		if (!$this->getOption('value'))
		{
			return;
		}

		$query->where($this->config->getDataModel()->getTable().'.'.$this->getOption('field_name'), 'LIKE', '%' . $this->getOption('value') . '%');
	}
}