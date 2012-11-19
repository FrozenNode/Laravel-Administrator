<?php
namespace Admin\Libraries\Fields\Relationships;

class HasMany extends Relationship {

	/**
	 * This determines if there are potentially multiple related values (i.e. whether to use an array of items or just a single value)
	 *
	 * @var bool
	 */
	public $multipleValues = true;

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

		$relationship = $model->{$field}();

		$this->table = $relationship->table->from;
		$this->column = $relationship->table->wheres[0]['column'];
	}

	/**
	 * Filters a query object with this item's data given a model
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

		//$joins[] = $relation['table'];
		$query->join($this->table, $model->table().'.'.$model::$key, '=', $this->table.'.'.$this->column);
		$query->where_in($this->table.'.id', (is_array($this->value) ? $this->value : array($this->value)));
	}
}