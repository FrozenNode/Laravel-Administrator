<?php
namespace Admin\Libraries\Fields\Relationships;

use Admin\Libraries\Column;

class HasOne extends Relationship {

	/**
	 * If this is true, the field is editable
	 *
	 * @var bool
	 */
	public $editable = false;

	/**
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param Eloquent 		$model
	 */
	public function __construct($field, $info, $model)
	{
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

		//if the table hasn't been joined yet, join it
		if (!Column::isJoined($query, $this->table))
		{
			$query->join($this->table, $model->table().'.'.$model::$key, '=', $this->table.'.'.$this->column);
		}

		$query->where_in($this->table.'.id', (is_array($this->value) ? $this->value : array($this->value)));
	}

	/**
	 * For the moment this is an empty function until I can figure out a way to display HasOne and HasMany relationships on this model
	 *
	 * @param Eloquent	$model
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input) {}
}