<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class HasMany extends Relationship {

	/**
	 * This determines if there are potentially multiple related values (i.e. whether to use an array of items or just a single value)
	 *
	 * @var bool
	 */
	public $multipleValues = true;

	/**
	 * If this is true, the field is editable
	 *
	 * @var bool
	 */
	public $editable = false;

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

		$relationship = $model->{$field}();
		$related_model = $relationship->getRelated();

		$this->table = $related_model->getTable();
		$this->column = $relationship->getPlainForeignKey();
	}

	/**
	 * Filters a query object with this item's data (currently empty because there's no easy way to represent this)
	 *
	 * @param Query		$query
	 * @param array		$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$query, &$selects = null) {}

	/**
	 * For the moment this is an empty function until I can figure out a way to display HasOne and HasMany relationships on this model
	 *
	 * @param Eloquent	$model
	 *
	 * @return array
	 */
	public function fillModel(&$model, $input) {}
}