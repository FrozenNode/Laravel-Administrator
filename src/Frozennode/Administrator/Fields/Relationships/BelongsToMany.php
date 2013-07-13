<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class BelongsToMany extends Relationship {

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = array(
		'column2' => '',
		'multiple_values' => true,
		'sort_field' => false,
	);

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
		$relationship = $model->{$this->getOption('field_name')}();
		$related_model = $relationship->getRelated();

		$this->userOptions['table'] = $relationship->getTable();
		$this->userOptions['column'] = $relationship->getForeignKey();
		$this->userOptions['column2'] = $relationship->getOtherKey();
		$this->userOptions['foreign_key'] = $related_model->getKeyName();
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
		$fieldName = $this->getOption('field_name');

		//if this field is sortable, delete all the old records and insert the new ones one at a time
		if ($sortField = $this->getOption('sort_field'))
		{
			//first delete all the old records
			$model->{$fieldName}()->delete();

			foreach ($input as $i => $item)
			{
				$model->{$fieldName}()->attach($item, array($sortField => $i));
			}
		}
		else
		{
			$model->{$fieldName}()->sync($input);
		}

		$model->__unset($fieldName);
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

		//get the values
		$value = $this->getOption('value');
		$table = $this->getOption('table');
		$column = $this->getOption('column');
		$column2 = $this->getOption('column2');

		//if there is no value, return
		if (!$value)
		{
			return;
		}

		$model = $this->config->getDataModel();

		//if the table hasn't been joined yet, join it
		if (!$this->validator->isJoined($query, $table))
		{
			$query->join($table, $model->getTable().'.'.$model->getKeyName(), '=', $column);
		}

		//add where clause
		$query->whereIn($column2, $value);

		//add having clauses
		$query->havingRaw('COUNT(DISTINCT '.$column2.') = '. count($value));

		//add select field
		if ($selects && !in_array($column2, $selects))
		{
			$selects[] = $column2;
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
		if (!$this->validator->isJoined($query, $this->getOption('table')))
		{
			$query->join($this->getOption('table'), $model->getTable().'.'.$model->getKeyName(), '=', $this->getOption('column2'));
		}

		$query->where($this->getOption('column'), '=', $constraint);
	}
}