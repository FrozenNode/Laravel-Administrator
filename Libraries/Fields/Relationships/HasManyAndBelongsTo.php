<?php
namespace Admin\Libraries\Fields\Relationships;

use Admin\Libraries\Column;

class HasManyAndBelongsTo extends Relationship {


	/**
	 * The field type which matches a $fieldTypes key
	 *
	 * @var string
	 */
	public $column2 = '';

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
	 * @param ModelConfig 	$config
	 */
	public function __construct($field, $info, $config)
	{
		parent::__construct($field, $info, $config);

		//set up the model depending on what's passed in
		$model = is_a($config, 'Admin\\Libraries\\ModelConfig') ? $config->model : $config;

		$relationship = $model->{$field}();
		$table = $relationship->table->joins[0];
		$related_model = $relationship->model;

		$this->table = $table->table;
		$this->column = $relationship->table->wheres[0]['column'];
		$this->column2 = $table->clauses[0]['column2'];
		$this->foreignKey = $related_model::$key;
	}


	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['column2'] = $this->column2;

		return $arr;
	}

	/**
	 * Fill a model with input data
	 *
	 * @param Eloquent	$model
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input)
	{
		$input = $input && is_array($input) ? $input : array();

		$model->{$this->field}()->sync($input);
		unset($model->attributes[$this->field]);
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
			$query->join($this->table, $model->table().'.'.$model::$key, '=', $this->column);
		}

		$query->where_in($this->column2, $this->value);
	}

	/**
	 * Constrains a query by a given set of constraints
	 *
	 * @param  Query 		$query
	 * @param  Eloquent 	$model
	 * @param  array 		$constraints
	 *
	 * @return void
	 */
	public function constrainQuery(&$query, $model, $constraints)
	{
		//if the column hasn't been joined yet, join it
		if (!Column::isJoined($query, $this->table))
		{
			$query->join($this->table, $model->table().'.'.$model::$key, '=', $this->column2);
		}

		$query->where($this->column, '=', $constraints);
	}
}