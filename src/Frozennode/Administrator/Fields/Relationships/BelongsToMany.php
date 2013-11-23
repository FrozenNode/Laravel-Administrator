<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
	 * Builds a few basic options
	 */
	public function build()
	{
		parent::build();

		$options = $this->suppliedOptions;

		$model = $this->config->getDataModel();
		$relationship = $model->{$options['field_name']}();
		$relatedModel = $relationship->getRelated();

		$options['table'] = $relationship->getTable();
		$options['column'] = $relationship->getForeignKey();
		$options['column2'] = $relationship->getOtherKey();
		$options['foreign_key'] = $relatedModel->getKeyName();

		$this->suppliedOptions = $options;
	}

	/**
	 * Fill a model with input data
	 *
	 * @param \Illuminate\Database\Eloquent\Model	$model
	 * @param mixed									$input
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input)
	{
		$input = $input ? explode(',', $input) : array();
		$fieldName = $this->getOption('field_name');
		$relationship = $model->{$fieldName}();

		//if this field is sortable, delete all the old records and insert the new ones one at a time
		if ($sortField = $this->getOption('sort_field'))
		{
			//first delete all the old records
			$relationship->detach();

			//then re-attach them in the correct order
			foreach ($input as $i => $item)
			{
				$relationship->attach($item, array($sortField => $i));
			}
		}
		else
		{
			//elsewise the order doesn't matter, so use sync
			$relationship->sync($input);
		}

		//unset the attribute on the model
		$model->__unset($fieldName);
	}


	/**
	 * Filters a query object with this item's data
	 *
	 * @param \Illuminate\Database\Query\Builder	$query
	 * @param array									$selects
	 *
	 * @return void
	 */
	public function filterQuery(QueryBuilder &$query, &$selects = null)
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
		$query->havingRaw('COUNT(DISTINCT ' . $query->getConnection()->getTablePrefix() . $column2 . ') = ' . count($value));

		//add select field
		if ($selects && !in_array($column2, $selects))
		{
			$selects[] = $column2;
		}
	}

	/**
	 * Constrains a query by a given set of constraints
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder	$query
	 * @param  \Illuminate\Database\Eloquent\Model 		$relatedModel
	 * @param  string 									$constraint
	 *
	 * @return void
	 */
	public function constrainQuery(EloquentBuilder &$query, $relatedModel, $constraint)
	{
		//if the column hasn't been joined yet, join it
		if (!$this->validator->isJoined($query, $this->getOption('table')))
		{
			$query->join($this->getOption('table'), $relatedModel->getTable().'.'.$relatedModel->getKeyName(), '=', $this->getOption('column2'));
		}

		$query->where($this->getOption('column'), '=', $constraint);
	}
}
