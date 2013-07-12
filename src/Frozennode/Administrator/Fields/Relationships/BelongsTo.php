<?php
namespace Frozennode\Administrator\Fields\Relationships;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

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
	 * Create a new BelongsTo instance
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
		$otherModel = $relationship->getRelated();

		$this->table = $otherModel->getTable();
		$this->column = $otherModel->getKeyName();
		$this->foreignKey = $relationship->getForeignKey();
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
		$model->{$this->foreignKey} = $input !== 'false' ? $input : null;

		$model->__unset($this->field);
	}

	/**
	 * Filters a query object with this item's data given a model
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

		$query->where($this->foreignKey, '=', $this->value);
	}

}