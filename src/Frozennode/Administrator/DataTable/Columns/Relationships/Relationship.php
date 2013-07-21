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

	/**
	 * Sets up the existing relationship wheres
	 *
	 * @param Illuminate\Database\Eloquent\Relations\Relation		$relationship
	 * @param string												$tableAlias
	 *
	 * @return string
	 */
	public function getRelationshipWheres($relationship, $tableAlias)
	{
		//get the query instance
		$query = $relationship->getQuery()->getQuery();

		//get the connection instance
		$connection = $query->getConnection();

		//fetch the where values and their bindings
		array_shift($query->wheres);

		//iterate over the wheres to properly alias the columns
		foreach ($query->wheres as &$where)
		{
			$where['column'] = $tableAlias . '.' . $where['column'];
		}

		$sql = $query->toSql();
		$fullQuery = $this->interpolateQuery($sql, $connection->prepareBindings($query->getBindings()));
		$split = explode(' where ', $fullQuery);
		return isset($split[1]) ? $split[1] : '';
	}

	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter.
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 *
	 * @return string The interpolated query
	 */
	public function interpolateQuery($query, array $params) {
		$keys = array();
		$values = $params;

		//build a regular expression for each parameter
		foreach ($params as $key => $value) {
			if (is_string($key)) {
				$keys[] = "/:" . $key . "/";
			} else {
				$keys[] = '/[?]/';
			}

			if (is_string($value))
				$values[$key] = "'" . $value . "'";

			if (is_array($value))
				$values[$key] = implode(',', $value);

			if (is_null($value))
				$values[$key] = 'NULL';
		}

		$query = preg_replace($keys, $values, $query, 1, $count);

		return $query;
	}

}