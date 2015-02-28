<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Illuminate\Database\Query\Builder as QueryBuilder;

class HasMany extends HasOneOrMany {

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
	 * Fill a model with input data
	 *
	 * @param \Illuminate\Database\Eloquent\Model	$model
	 * @param mixed									$input
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input)
	{
		// $input is an array of all foreign key IDs
		//
		// $model is the model for which the above answers should be associated to
		$fieldName = $this->getOption('field_name');
		$input = $input ? explode(',', $input) : array();
		$relationship = $model->{$fieldName}();

		// get the plain foreign key so we can set it to null:
		$fkey = $relationship->getPlainForeignKey();

		$relatedObjectClass = get_class($relationship->getRelated());

		// first we "forget all the related models" (by setting their foreign key to null)
		foreach($relationship->get() as $related)
		{
			$related->$fkey = null; // disassociate
			$related->save();
		}

		// now associate new ones: (setting the correct order as well)
		$i = 0;
		foreach($input as $foreign_id)
		{
			$relatedObject = call_user_func($relatedObjectClass .'::find', $foreign_id);
			if ($sortField = $this->getOption('sort_field'))
			{
				$relatedObject->$sortField = $i++;
			}

			$relationship->save($relatedObject);
		}
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

}