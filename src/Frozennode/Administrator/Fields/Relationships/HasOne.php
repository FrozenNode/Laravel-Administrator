<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Column;

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
	 * @param ModelConfig	$config
	 */
	public function __construct($field, $info, $config)
	{
		parent::__construct($field, $info, $config);

		//set up the model depending on what's passed in
		$model = is_a($config, 'Frozennode\\Administrator\\ModelConfig') ? $config->model : $config;

		$relationship = $model->{$field}();
		$related_model = $relationship->getRelated();

		$this->table = $related_model->getTable();
		$this->column = $relationship->getPlainForeignKey();
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
			$query->join($this->table, $model->getTable().'.'.$model->getKeyName(), '=', $this->table.'.'.$this->column);
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