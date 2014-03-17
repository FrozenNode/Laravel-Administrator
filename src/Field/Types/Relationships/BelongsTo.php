<?php namespace Frozennode\Administrator\Field\Types\Relationships;

use Illuminate\Database\Query\Builder as QueryBuilder;

class BelongsTo extends Relationship {

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaultOptions = [
		'external' => false
	];

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'belongs_to';
	}

	/**
	 * Builds a few basic options
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		$options = parent::buildOptions($options);

		$model = $this->config->getDataModel();
		$relationship = $model->{$options['field_name']}();
		$relatedModel = $relationship->getRelated();

		$options['table'] = $relatedModel->getTable();
		$options['column'] = $relatedModel->getKeyName();
		$options['foreign_key'] = $relationship->getForeignKey();

		return $options;
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
		$model->{$this->getOption('foreign_key')} = $input !== 'false' ? $input : null;

		$model->__unset($this->getOption('field_name'));
	}

	/**
	 * Filters a query object with this item's data given a model
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

		//if there is no value, return
		if (!$this->getOption('value'))
		{
			return;
		}

		$query->where($this->getOption('foreign_key'), '=', $this->getOption('value'));
	}

}