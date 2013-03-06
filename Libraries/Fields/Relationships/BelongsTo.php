<?php
namespace Admin\Libraries\Fields\Relationships;

class BelongsTo extends Relationship {

	/**
	 * Determines if this column is a normal field on this table
	 *
	 * @var string
	 */
	public $foreignKey;

	/**
	 * If this is true, the field is an external field (i.e. it's a relationship but not a belongs_to)
	 *
	 * @var bool
	 */
	public $external = false;

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
		$otherModel = $relationship->model;

		$this->table = $otherModel->table();
		$this->column = $otherModel::$key;
		$this->foreignKey = $relationship->foreign;
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
		$model->{$this->foreignKey} = $input;
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

		$query->where($this->foreignKey, '=', $this->value);
	}

}