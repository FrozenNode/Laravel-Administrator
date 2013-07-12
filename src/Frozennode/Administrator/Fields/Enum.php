<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class Enum extends Field {

	/**
	 * The options used for the enum field
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Create a new Enum instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		parent::__construct($validator, $config, $db, $options);

		$options = $this->validator->arrayGet($options, 'options', $this->options);

		//iterate over the options to create the options assoc array
		foreach ($options as $val => $text)
		{
			$this->options[] = array(
				'id' => is_numeric($val) ? $text : $val,
				'text' => $text,
			);
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
		$model->{$this->field} = $input;
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

		$this->value = $this->value === '' ? null : $this->value;
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
		//run the parent method
		parent::filterQuery($query, $selects);

		//if there is no value, return
		if (!$this->value)
		{
			return;
		}

		$query->where($this->config->getDataModel()->getTable().'.'.$this->field, '=', $this->value);
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['options'] = $this->options;

		return $arr;
	}
}