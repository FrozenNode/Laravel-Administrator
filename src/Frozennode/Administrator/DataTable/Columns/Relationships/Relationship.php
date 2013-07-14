<?php
namespace Frozennode\Administrator\DataTable\Columns\Relationships;

use Frozennode\Administrator\DataTable\Columns\Column;

/**
 * The Column class helps us construct columns from models. It can be used to derive column information from a model, or it can be
 * instantiated to hold information about any given column.
 */
class Relationship extends Column {

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array(
		'is_related' => true,
		'external' => true
	);

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = array();

	/**
	 * Builds the necessary fields on the object
	 *
	 * @return void
	 */
	public function build()
	{
		$model = $this->config->getDataModel();
		$options = $this->suppliedOptions;
		$this->tablePrefix = $this->db->getTablePrefix();

		$relationship = $model->{$options['relationship']}();
		$relevant_model = $model;
		$selectTable = $options['column_name'] . '_' . $this->tablePrefix . $relationship->getRelated()->getTable();

		//set the relationship object so we can use it later
		$this->relationshipObject = $relationship;

		//replace the (:table) with the generated $selectTable
		$options['select'] = str_replace('(:table)', $selectTable, $options['select']);

		$this->suppliedOptions = $options;
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		$defaults = parent::getDefaults();

		return array_merge($defaults, $this->relationshipDefaults);
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getIncludedColumn()
	{
		return array();
	}

}