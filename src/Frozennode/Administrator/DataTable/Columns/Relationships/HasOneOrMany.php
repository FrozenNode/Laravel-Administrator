<?php
namespace Frozennode\Administrator\DataTable\Columns\Relationships;

class HasOneOrMany extends Relationship {

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
		$from_table = $this->tablePrefix . $relationship->getRelated()->getTable();
		$field_table = $columnName . '_' . $from_table;

		//grab the existing where clauses that the user may have set on the relationship
		$relationshipWheres = $this->getRelationshipWheres($relationship, $field_table);

		$where = $this->tablePrefix . $model->getTable() . '.' . $model->getKeyName() .
				' = ' .
				$field_table . '.' . $relationship->getPlainForeignKey()
				. ($relationshipWheres ? ' AND ' . $relationshipWheres : '');

		$selects[] = $this->db->raw("(SELECT " . $this->getOption('select') . "
										FROM " . $from_table." AS " . $field_table . ' ' . $joins . "
										WHERE " . $where . ") AS " . $this->db->getQueryGrammar()->wrap($columnName));
	}
}