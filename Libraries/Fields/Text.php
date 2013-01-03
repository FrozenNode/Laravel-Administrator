<?php
namespace Admin\Libraries\Fields;

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
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param Eloquent 		$model
	 */
	public function __construct($field, $info, $model)
	{
		parent::__construct($field, $info, $model);

		$this->limit = array_get($info, 'limit', $this->limit);
	}

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

		$query->where($model->table().'.'.$this->field, 'LIKE', '%' . $this->value . '%');
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