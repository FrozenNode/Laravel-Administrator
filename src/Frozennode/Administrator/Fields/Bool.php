<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class Bool extends Field {

	/**
	 * The value (used in filter)
	 *
	 * @var bool
	 */
	public $value = false;

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
	 * Sets the filter options for this item
	 *
	 * @param array		$filter
	 *
	 * @return void
	 */
	public function setFilter($filter)
	{
		parent::setFilter($filter);

		$this->value = $this->validator->arrayGet($filter, 'value', '');

		//if it isn't null, we have to check the 'true'/'false' string
		if ($this->value !== '')
		{
			$this->value = $this->value === 'true' ? 1 : 0;
		}
	}

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
		//if the field isn't empty
		if ($this->value !== '')
		{
			$query->where($this->config->getDataModel()->getTable().'.'.$this->field, '=', $this->value);
		}
	}
}