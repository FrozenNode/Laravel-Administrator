<?php
namespace Frozennode\Administrator\DataTable\Columns\Relationships;

class BelongsToMany extends Relationship {

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = array(
		'belongs_to_many' => true
	);

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

		$relationship = $model->{$this->getOption('relationship')}();
		$from_table = $this->tablePrefix . $model->getTable();
		$field_table = $columnName . '_' . $from_table;
		$other_table = $this->tablePrefix . $relationship->getRelated()->getTable();
		$other_alias = $columnName . '_' . $other_table;
		$other_model = $relationship->getRelated();
		$other_key = $other_model->getKeyName();
		$int_table = $this->tablePrefix . $relationship->getTable();
		$int_alias = $columnName . '_' . $int_table;
		$column1 = explode('.', $relationship->getForeignKey());
		$column1 = $column1[1];
		$column2 = explode('.', $relationship->getOtherKey());
		$column2 = $column2[1];
		$joins .= ' LEFT JOIN '.$int_table.' AS '.$int_alias.' ON '.$int_alias.'.'.$column1.' = '.$field_table.'.'.$model->getKeyName()
				.' LEFT JOIN '.$other_table.' AS '.$other_alias.' ON '.$other_alias.'.'.$other_key.' = '.$int_alias.'.'.$column2;

		//grab the existing where clauses that the user may have set on the relationship
		$relationshipWheres = $this->getRelationshipWheres($relationship, $other_alias, $int_alias, $int_table);

		$where = $this->tablePrefix . $model->getTable() . '.' . $model->getKeyName() . ' = ' . $int_alias . '.' . $column1
					. ($relationshipWheres ? ' AND ' . $relationshipWheres : '');

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
		$fk = $model->{$this->getOption('relationship')}()->getRelated()->getKeyName();

		return array($fk => $model->getTable() . '.' . $fk);
	}
}