<?php
namespace Frozennode\Administrator\DataTable\Columns\Relationships;

class BelongsTo extends Relationship {

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = array(
		'external' => false
	);

	/**
	 * The class name of a BelongsTo relationship
	 *
	 * @var string
	 */
	const BELONGS_TO = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';

	/**
	 * Builds the necessary fields on the object
	 *
	 * @return void
	 */
	public function build()
	{
		$options = $this->suppliedOptions;
		$this->tablePrefix = $this->db->getTablePrefix();
		$nested = $this->getNestedRelationships($options['relationship']);

		$relevantName = $nested['pieces'][sizeof($nested['pieces'])-1];
		$relevantModel = $nested['models'][sizeof($nested['models'])-2];
		$options['nested'] = $nested;

		$relationship = $relevantModel->{$relevantName}();
		$selectTable = $options['column_name'] . '_' . $this->tablePrefix . $relationship->getRelated()->getTable();

		//set the relationship object so we can use it later
		$this->relationshipObject = $relationship;

		//replace the (:table) with the generated $selectTable
		$options['select'] = str_replace('(:table)', $selectTable, $options['select']);

		$this->suppliedOptions = $options;
	}

	/**
	 * Converts the relationship key
	 *
	 * @param string		$name 	//the relationship name
	 *
	 * @return false|array('models' => array(), 'pieces' => array())
	 */
	public function getNestedRelationships($name)
	{
		$pieces = explode('.', $name);
		$models = array();
		$num_pieces = sizeof($pieces);

		//iterate over the relationships to see if they're all valid
		foreach ($pieces as $i => $rel)
		{
			//if this is the first item, then the model is the config's model
			if ($i === 0)
			{
				$models[] = $this->config->getDataModel();
			}

			//if the model method doesn't exist for any of the pieces along the way, exit out
			if (!method_exists($models[$i], $rel) || !is_a($models[$i]->{$rel}(), self::BELONGS_TO))
			{
				throw new \InvalidArgumentException("The '" . $this->getOption('column_name') . "' column in your " . $this->config->getOption('name') .
					" model configuration needs to be either a belongsTo relationship method name or a sequence of them connected with a '.'");
			}

			//we don't need the model of the last item
			$models[] = $models[$i]->{$rel}()->getRelated();
		}

		return array('models' => $models, 'pieces' => $pieces);
	}

	/**
	 * Adds selects to a query
	 *
	 * @param array 	$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$selects)
	{
		$model = $this->config->getDataModel();
		$joins = $where = '';
		$columnName = $this->getOption('column_name');
		$nested = $this->getOption('nested');
		$num_pieces = sizeof($nested['pieces']);

		//if there is more than one nested relationship, we need to join all the tables
		if ($num_pieces > 1)
		{
			for ($i = 1; $i < $num_pieces; $i++)
			{
				$model = $nested['models'][$i];
				$relationship = $model->{$nested['pieces'][$i]}();
				$relationship_model = $relationship->getRelated();
				$table = $this->tablePrefix . $relationship_model->getTable();
				$alias = $columnName . '_' . $table;
				$last_alias = $columnName . '_' . $this->tablePrefix . $model->getTable();
				$joins .= ' LEFT JOIN ' . $table . ' AS ' . $alias .
							' ON ' . $alias . '.' . $relationship->getRelated()->getKeyName() .
								' = ' . $last_alias . '.' . $relationship->getForeignKey();
			}
		}

		$first_model = $nested['models'][0];
		$first_piece = $nested['pieces'][0];
		$first_relationship = $first_model->{$first_piece}();
		$relationship_model = $first_relationship->getRelated();
		$from_table = $this->tablePrefix . $relationship_model->getTable();
		$field_table = $columnName . '_' . $from_table;

		$where = $this->tablePrefix . $first_model->getTable() . '.' . $first_relationship->getForeignKey() .
					' = ' .
					$field_table . '.' . $relationship_model->getKeyName();

		$selects[] = $this->db->raw("(SELECT " . $this->getOption('select') . "
										FROM " . $from_table." AS " . $field_table . ' ' . $joins . "
										WHERE " . $where . ") AS " . $this->db->getQueryGrammar()->wrap($columnName));
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getIncludedColumn()
	{
		$model = $this->config->getDataModel();
		$nested = $this->getOption('nested');
		$fk = $nested['models'][0]->{$nested['pieces'][0]}()->getForeignKey();

		return array($fk => $model->getTable() . '.' . $fk);
	}
}