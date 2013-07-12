<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class BelongsToMany extends Relationship {


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
	 * Create a new BelongsToMany instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		parent::__construct($validator, $config, $db, $options);

		//set up the model depending on what's passed in
		$model = $this->config->getDataModel();

		$relationship = $model->{$this->field}();
		$related_model = $relationship->getRelated();

		$this->table = $relationship->getTable();
		$this->column = $relationship->getForeignKey();
		$this->column2 = $relationship->getOtherKey();
		$this->foreignKey = $related_model->getKeyName();
		$this->sortField = $this->validator->arrayGet($options, 'sort_field', $this->sortField);
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
	 * @param Illuminate\Database\Eloquent\Model	$model
	 * @param mixed									$input
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

		$model->__unset($this->field);
	}


	/**
	 * Filters a query object with this item's data
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

		$model = $this->config->getDataModel();

		//if the table hasn't been joined yet, join it
		if (!$this->validator->isJoined($query, $this->table))
		{
			$query->join($this->table, $model->getTable().'.'.$model->getKeyName(), '=', $this->column);
		}

		//add where clause
		$query->whereIn($this->column2, $this->value);

		//add having clauses
		$query->havingRaw('COUNT(DISTINCT '.$this->column2.') = '. count($this->value));

		//add select field
		if ($selects && !in_array($this->column2, $selects))
		{
			$selects[] = $this->column2;
		}
	}

	/**
	 * Constrains a query by a given set of constraints
	 *
	 * @param  Query 								$query
	 * @param  Illuminate\Database\Eloquent\Model 	$model
	 * @param  string 								$constraint
	 *
	 * @return void
	 */
	public function constrainQuery(&$query, $model, $constraint)
	{
		//if the column hasn't been joined yet, join it
		if (!$this->validator->isJoined($query, $this->table))
		{
			$query->join($this->table, $model->getTable().'.'.$model->getKeyName(), '=', $this->column2);
		}

		$query->where($this->column, '=', $constraint);
	}
}