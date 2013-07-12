<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class Text extends Field {

	/**
	 * The character limit
	 *
	 * @var string
	 */
	public $limit = 0;

	/**
	 * The starting height of the textarea (if applicable)
	 *
	 * @var string
	 */
	public $height = 100;

	/**
	 * Create a new Text instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		parent::__construct($validator, $config, $db, $options);

		$this->limit = $this->validator->arrayGet($options, 'limit', $this->limit);
		$this->height = $this->validator->arrayGet($options, 'height', $this->height);
	}

	/**
	 * Filters a query object given
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

		$query->where($this->config->getDataModel()->getTable().'.'.$this->field, 'LIKE', '%' . $this->value . '%');
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['limit'] = $this->limit;
		$arr['height'] = $this->height;

		return $arr;
	}
}