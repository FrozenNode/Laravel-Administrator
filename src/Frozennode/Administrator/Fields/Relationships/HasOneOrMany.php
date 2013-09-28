<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class HasOneOrMany extends Relationship {

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = array(
		'editable' => false,
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
		$related_model = $relationship->getRelated();

		$options['table'] = $related_model->getTable();
		$options['column'] = $relationship->getPlainForeignKey();

		$this->suppliedOptions = $options;
	}

	/**
	 * Filters a query object with this item's data (currently empty because there's no easy way to represent this)
	 *
	 * @param \Illuminate\Database\Query\Builder	$query
	 * @param array									$selects
	 *
	 * @return void
	 */
	public function filterQuery(QueryBuilder &$query, &$selects = null) {}

	/**
	 * For the moment this is an empty function until I can figure out a way to display HasOne and HasMany relationships on this model
	 *
	 * @param \Illuminate\Database\Eloquent\Model	$model
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input) {}

	/**
	 * Constrains a query by a given set of constraints
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder	$query
	 * @param  \Illuminate\Database\Eloquent\Model	 	$relatedModel
	 * @param  string 									$constraint
	 *
	 * @return void
	 */
	public function constrainQuery(EloquentBuilder &$query, $relatedModel, $constraint)
	{
		$query->where($this->getOption('column'), '=', $constraint);
	}
}