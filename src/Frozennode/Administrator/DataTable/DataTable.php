<?php
namespace Frozennode\Administrator\DataTable;

use Frozennode\Administrator\Config\ConfigInterface;
use Frozennode\Administrator\DataTable\Columns\Factory as ColumnFactory;
use Frozennode\Administrator\Fields\Factory as FieldFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\DatabaseManager as DB;

class DataTable {

	/**
	 * The config instance
	 *
	 * @var \Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The validator instance
	 *
	 * @var \Frozennode\Administrator\DataTable\Columns\Factory
	 */
	protected $columnFactory;

	/**
	 * The validator instance
	 *
	 * @var \Frozennode\Administrator\Fields\Factory
	 */
	protected $fieldFactory;

	/**
	 * The column objects
	 *
	 * @var array
	 */
	protected $columns;

	/**
	 * The sort options
	 *
	 * @var array
	 */
	protected $sort;

	/**
	 * The number of rows per page for this data table
	 *
	 * @var int
	 */
	protected $rowsPerPage = 20;

	/**
	 * Create a new action DataTable instance
	 *
	 * @param \Frozennode\Administrator\Config\ConfigInterface		$config
	 * @param \Frozennode\Administrator\DataTable\Columns\Factory	$columnFactory
	 * @param \Frozennode\Administrator\Fields\Factory				$fieldFactory
	 */
	public function __construct(ConfigInterface $config, ColumnFactory $columnFactory, FieldFactory $fieldFactory)
	{
		//set the config, and then validate it
		$this->config = $config;
		$this->columnFactory = $columnFactory;
		$this->fieldFactory = $fieldFactory;
	}

	/**
	 * Builds a results array (with results and pagination info)
	 *
	 * @param \Illuminate\Database\DatabaseManager 	$db
	 * @param array									$filters
	 * @param int									$page
	 * @param array									$sort (with 'field' and 'direction' keys)
	 *
	 * @return array
	 */
	public function getRows(DB $db, $filters = null, $page = 1, $sort = null)
	{
		//prepare the query
		extract($this->prepareQuery($db, $page, $sort, $filters));

		//run the count query
		$output = $this->performCountQuery($countQuery, $querySql, $page);

		//now we need to limit and offset the rows in remembrance of our dear lost friend paginate()
		$query->take($this->rowsPerPage);
		$query->skip($this->rowsPerPage * ($output['page'] === 0 ? $output['page'] : $output['page'] - 1));

		//parse the results
		$output['results'] = $this->parseResults($query->get());

		return $output;
	}

	/**
	 * Builds a results array (with results and pagination info)
	 *
	 * @param \Illuminate\Database\DatabaseManager 	$db
	 * @param int									$page
	 * @param array									$sort (with 'field' and 'direction' keys)
	 * @param array									$filters
	 *
	 * @return array
	 */
	public function prepareQuery(DB $db, $page = 1, $sort = null, $filters = null)
	{
		//grab the model instance
		$model = $this->config->getDataModel();

		//update the sort options
		$this->setSort($sort);
		$sort = $this->getSort();

		//get things going by grouping the set
		$table = $model->getTable();
		$keyName = $model->getKeyName();
		$query = $model->groupBy($table . '.' . $keyName);

		//get the Illuminate\Database\Query\Builder instance and set up the count query
		$dbQuery = $query->getQuery();
		$countQuery = $dbQuery->getConnection()->table($table)->groupBy($table . '.' . $keyName);

		//run the supplied query filter for both queries if it was provided
		$this->config->runQueryFilter($dbQuery);
		$this->config->runQueryFilter($countQuery);

		//set up initial array states for the selects
		$selects = array($table.'.*');

		//set the filters
		$this->setFilters($filters, $dbQuery, $countQuery, $selects);

		//set the selects
		$dbQuery->select($selects);

		//determines if the sort should have the table prefixed to it
		$sortOnTable = true;

		//get the columns
		$columns = $this->columnFactory->getColumns();

		//iterate over the columns to check if we need to join any values or add any extra columns
		foreach ($columns as $column)
		{
			//if this is a related column, we'll need to add some selects
			$column->filterQuery($selects);

			//if this is a related field or
			if ( ($column->getOption('is_related') || $column->getOption('select')) && $column->getOption('column_name') === $sort['field'])
			{
				$sortOnTable = false;
			}
		}

		//if the sort is on the model's table, prefix the table name to it
		if ($sortOnTable)
		{
			$sort['field'] = $table . '.' . $sort['field'];
		}

		//grab the query sql for later
		$querySql = $query->toSql();

		//order the set by the model table's id
		$query->orderBy($sort['field'], $sort['direction']);

		//then retrieve the rows
		$query->getQuery()->select($selects);

		//only select distinct rows
		$query->distinct();

		return compact('query', 'querySql', 'countQuery', 'sort', 'selects');
	}

	/**
	 * Performs the count query and returns info about the pages
	 *
	 * @param \Illuminate\Database\Query\Builder	$countQuery
	 * @param string								$querySql
	 * @param int									$page
	 *
	 * @return array
	 */
	public function performCountQuery(QueryBuilder $countQuery, $querySql, $page)
	{
		//grab the model instance
		$model = $this->config->getDataModel();

		//then wrap the inner table and perform the count
		$sql = "SELECT COUNT({$model->getKeyName()}) AS aggregate FROM ({$querySql}) AS agg";

		//then perform the count query
		$results = $countQuery->getConnection()->select($sql, $countQuery->getBindings());
		$numRows = $results[0]->aggregate;
		$page = (int) $page;
		$last = (int) ceil($numRows / $this->rowsPerPage);

		return array(
			//if the current page is greater than the last page, set the current page to the last page
			'page' => $page > $last ? $last : $page,
			'last' => $last,
			'total' => $numRows,
		);
	}

	/**
	 * Sets the query filters when getting the rows
	 *
	 * @param mixed									$filters
	 * @param \Illuminate\Database\Query\Builder	$query
	 * @param \Illuminate\Database\Query\Builder	$countQuery
	 * @param array									$selects
	 */
	public function setFilters($filters, QueryBuilder &$query, QueryBuilder &$countQuery, &$selects)
	{
		//then we set the filters
		if ($filters && is_array($filters))
		{
			foreach ($filters as $filter)
			{
				//get the field object
				$fieldObject = $this->fieldFactory->findFilter($filter['field_name']);

				//set the filter on the object
				$fieldObject->setFilter($filter);

				//filter the query objects, only pass in the selects the first time so they aren't added twice
				$fieldObject->filterQuery($query, $selects);
				$fieldObject->filterQuery($countQuery);
			}
		}
	}

	/**
	 * Parses the results of a getRows query and converts it into a manageable array with the proper rendering
	 *
	 * @param 	Collection	$rows
	 *
	 * @return	array
	 */
	public function parseResults($rows)
	{
		$results = array();

		//convert the resulting set into arrays
		foreach ($rows as $item)
		{
			//iterate over the included and related columns
			$arr = array();

			$this->parseOnTableColumns($item, $arr);

			//then grab the computed, unsortable columns
			$this->parseComputedColumns($item, $arr);

			$results[] = $arr;
		}

		return $results;
	}

	/**
	 * Goes through all related columns and sets the proper values for this row
	 *
	 * @param \Illuminate\Database\Eloquent\Model	$item
	 * @param array									$outputRow
	 *
	 * @return void
	 */
	public function parseOnTableColumns($item, array &$outputRow)
	{
		$columns = $this->columnFactory->getColumns();
		$includedColumns = $this->columnFactory->getIncludedColumns($this->fieldFactory->getEditFields());
		$relatedColumns = $this->columnFactory->getRelatedColumns();

		//loop over both the included and related columns
		foreach (array_merge($includedColumns, $relatedColumns) as $field => $col)
		{
			$attributeValue = $item->getAttribute($field);

			//if this column is in our objects array, render the output with the given value
			if (isset($columns[$field]))
			{
				$outputRow[$field] = array(
					'raw' => $attributeValue,
					'rendered' => $columns[$field]->renderOutput($attributeValue),
				);
			}
			//otherwise it's likely the primary key column which wasn't included (though it's needed for identification purposes)
			else
			{
				$outputRow[$field] = array(
					'raw' => $attributeValue,
					'rendered' => $attributeValue,
				);
			}
		}
	}

	/**
	 * Goes through all computed columns and sets the proper values for this row
	 *
	 * @param \Illuminate\Database\Eloquent\Model	$item
	 * @param array									$outputRow
	 *
	 * @return void
	 */
	public function parseComputedColumns($item, array &$outputRow)
	{
		$columns = $this->columnFactory->getColumns();
		$computedColumns = $this->columnFactory->getComputedColumns();

		//loop over the computed columns
		foreach ($computedColumns as $name => $column)
		{
			$outputRow[$name] = array(
				'raw' => $item->{$name},
				'rendered' => $columns[$name]->renderOutput($item->{$name}),
			);
		}
	}

	/**
	 * Sets up the sort options
	 *
	 * @param array		$sort
	 */
	public function setSort($sort = null)
	{
		$sort = $sort && is_array($sort) ? $sort : $this->config->getOption('sort');

		//set the sort values
		$this->sort = array(
			'field' => isset($sort['field']) ? $sort['field'] : $this->config->getDataModel()->getKeyName(),
			'direction' => isset($sort['direction']) ? $sort['direction'] : 'desc',
		);

		//if the sort direction isn't valid, set it to 'desc'
		if (!in_array($this->sort['direction'], array('asc', 'desc')))
		{
			$this->sort['direction'] = 'desc';
		}
	}

	/**
	 * Gets the sort options
	 *
	 * @return array
	 */
	public function getSort()
	{
		return $this->sort;
	}

	/**
	 * Set the number of rows per page for this data table
	 *
	 * @param \Illuminate\Session\Store	$session
	 * @param int						$globalPerPage
	 * @param int						$override	//if provided, this will set the session's rows per page value
	 */
	public function setRowsPerPage(\Illuminate\Session\Store $session, $globalPerPage, $override = null)
	{
		if ($override)
		{
			$perPage = (int) $override;
			$session->put('administrator_' . $this->config->getOption('name') . '_rows_per_page', $perPage);
		}

		$perPage = $session->get('administrator_' . $this->config->getOption('name') . '_rows_per_page');

		if (!$perPage)
		{
			$perPage = (int) $globalPerPage;
		}

		$this->rowsPerPage = $perPage;
	}

	/**
	 * Gets the rows per page
	 *
	 * @return int
	 */
	public function getRowsPerPage()
	{
		return $this->rowsPerPage;
	}
}
