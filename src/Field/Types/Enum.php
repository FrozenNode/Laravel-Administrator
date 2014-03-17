<?php namespace Frozennode\Administrator\Field\Types;

use Frozennode\Administrator\Field\Field;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Enum extends Field {

	/**
	 * The options used for the enum field
	 *
	 * @var array
	 */
	protected $rules = [
		'options' => 'required|array|not_empty',
	];

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'enum';
	}

	/**
	 * Builds a few basic options
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		$options = parent::buildOptions($options);

		$dataOptions = $options['options'];
		$options['options'] = [];

		//iterate over the options to create the options assoc array
		foreach ($dataOptions as $val => $text)
		{
			$options['options'][] = [
				'id' => is_numeric($val) ? $text : $val,
				'text' => $text,
			];
		}

		return $options;
	}

	/**
	 * Fill a model with input data
	 *
	 * @param \Illuminate\Database\Eloquent\model	$model
	 * @param mixed									$input
	 */
	public function fillModel(&$model, $input)
	{
		$model->{$this->getOption('field_name')} = $input;
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

		$this->userOptions['value'] = $this->getOption('value') === '' ? null : $this->getOption('value');
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