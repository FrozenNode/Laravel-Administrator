<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Column;

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
	 * If provided, the sort field is used to reorder values in the UI and then saved to the intermediate relationship table
	 *
	 * @var bool
	 */
	public $sortField = false;


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
		$model = is_a($config, 'Frozennode\\Administrator\\ModelConfig') ? $config->model : $config;

		$relationship = $model->{$field}();
		$related_model = $relationship->getRelated();

		$this->table = $relationship->getTable();
		$this->column = $relationship->getForeignKey();
		$this->column2 = $relationship->getOtherKey();
		$this->foreignKey = $related_model->getKeyName();
		$this->sortField = array_get($info, 'sort_field', $this->sortField);
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
		$arr['sort_field'] = $this->sortField;

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
		$input = $input ? explode(',', $input) : array();

		//if this field is sortable, delete all the old records and insert the new ones one at a time
		if ($this->sortField)
		{
			//first delete all the old records
			$model->{$this->field}()->delete();

			foreach ($input as $i => $item)
			{
				$model->{$this->field}()->attach($item, array($this->sortField => $i));
			}
		}
		else
		{
			$model->{$this->field}()->sync($input);
		}

		//then attach all of the new records
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
			$query->join($this->table, $model->getTable().'.'.$model->getKeyName(), '=', $this->column);
		}

		$query->whereIn($this->column2, $this->value);
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
			$query->join($this->table, $model->getTable().'.'.$model->getKeyName(), '=', $this->column2);
		}

		$query->where($this->column, '=', $constraints);
	}
}