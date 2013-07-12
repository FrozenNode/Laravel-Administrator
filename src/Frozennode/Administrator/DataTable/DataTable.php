<?php
namespace Frozennode\Administrator\DataTable;

use Frozennode\Administrator\Config\ConfigInterface;
use Frozennode\Administrator\DataTable\Columns\Factory as ColumnFactory;
use Frozennode\Administrator\Fields\Factory as FieldFactory;

class DataTable {

	/**
	 * The config instance
	 *
	 * @var Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\DataTable\Columns\Factory
	 */
	protected $columnFactory;

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\Fields\Factory
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
	 * @param Frozennode\Administrator\Config\ConfigInterface		$config
	 * @param Frozennode\Administrator\DataTable\Columns\Factory	$columnFactory
	 * @param Frozennode\Administrator\Fields\Factory				$fieldFactory
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
	 * @param Illuminate\Database\DatabaseManager 	$db
	 * @param int									$page
	 * @param array									$sort (with 'field' and 'direction' keys)
	 * @param array									$filters
	 */
	public function getRows(\Illuminate\Database\DatabaseManager $db, $page = 1, $sort = null, $filters = null)
	{
		//grab the model instance
		$model = $this->config->getDataModel();

		//update the sort options
		$this->setSort($sort);
		$sort = $this->sort;

		//get things going by grouping the set
		$query = $model::groupBy($model->getTable().'.'.$model->getKeyName());
		$db_query = $query->getQuery();
		$count_query = $db->table($model->getTable())->groupBy($model->getTable().'.'.$model->getKeyName());

		//set up initial array states for the selects
		$selects = array($model->getTable().'.*');

		//set the filters
		$this->setFilters($filters, $db_query, $count_query, $selects);

		//set the selects
		$db_query->select($selects);

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
			if ( ($column->isRelated || $column->select) && $column->field === $sort['field'])
			{
				$sortOnTable = false;
			}
		}

		//if the sort is on the model's table, prefix the table name to it
		if ($sortOnTable)
		{
			$sort['field'] = $model->getTable() . '.' . $sort['field'];
		}

		$sql = $query->toSql();

		//then wrap the inner table and perform the count
		$sql = "SELECT COUNT({$model->getKeyName()}) AS aggregate FROM ({$sql}) AS agg";

		//then perform the count query
		$results = $count_query->getConnection()->select($sql, $count_query->getBindings());
		$num_rows = $results[0]->aggregate;
		$page = (int) $page;
		$last = (int) ceil($num_rows / $this->rowsPerPage);

		//if the current page is greater than the last page, set the current page to the last page
		$page = $page > $last ? $last : $page;

		//now we need to limit and offset the rows in remembrance of our dear lost friend paginate()
		$query->take($this->rowsPerPage);
		$query->skip($this->rowsPerPage * ($page === 0 ? $page : $page - 1));

		//order the set by the model table's id
		$query->orderBy($sort['field'], $sort['direction']);

		//then retrieve the rows
		$query->getQuery()->select($selects);

		return array(
			'page' => $page,
			'last' => $last,
			'total' => $num_rows,
			'results' => $this->parseResults($query->distinct()->get()),
		);
	}

	/**
	 * Sets the query filters when getting the rows
	 *
	 * @param array		$filters
	 */
	public function setFilters($filters, &$db_query, &$count_query, &$selects)
	{
		//then we set the filters
		if ($filters && is_array($filters))
		{
			foreach ($filters as $filter)
			{
				//get the field object
				$fieldObject = $this->fieldFactory->findFilter($filter['field']);

				//set the filter on the object
				$fieldObject->setFilter($filter);

				//filter the query objects, only pass in the selects the first time so they aren't added twice
				$fieldObject->filterQuery($db_query, $selects);
				$fieldObject->filterQuery($count_query);
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
		$columns = $this->columnFactory->getColumns();
		$includedColumns = $this->columnFactory->getIncludedColumns($this->fieldFactory->getEditFields());
		$relatedColumns = $this->columnFactory->getRelatedColumns();
		$onTableColumns = array_merge($includedColumns, $relatedColumns);
		$computedColumns = $this->columnFactory->getComputedColumns();

		//convert the resulting set into arrays
		foreach ($rows as $item)
		{
			//iterate over the included and related columns
			$arr = array();

			foreach ($onTableColumns as $field => $col)
			{
				//if this column is in our objects array, render the output with the given value
				if (isset($columns[$field]))
				{
					$arr[$field] = array(
						'raw' => $item->getAttribute($field),
						'rendered' => $columns[$field]->renderOutput($item->getAttribute($field)),
					);
				}
				//otherwise it's likely the primary key column which wasn't included (though it's needed for identification purposes)
				else
				{
					$arr[$field] = array(
						'raw' => $item->getAttribute($field),
						'rendered' => $item->getAttribute($field),
					);
				}
			}

			//then grab the computed, unsortable columns
			foreach ($computedColumns as $col)
			{
				$arr[$col] = array(
					'raw' => $item->{$col},
					'rendered' => $columns[$col]->renderOutput($item->{$col}),
				);
			}

			$results[] = $arr;
		}

		return $results;
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
	 * @param Illuminate\Session\Store	$session
	 * @param int						$globalPerPage
	 * @param int						$override	//if provided, this will set the session's rows per page value
	 */
	public function setRowsPerPage(\Illuminate\Session\Store $session, $globalPerPage, $override = null)
	{
		if (is_int($override))
		{
			$session->put('administrator_' . $this->config->getOption('name') . '_rows_per_page', $override);
		}

		$perPage = $session->get('administrator_' . $this->config->getOption('name') . '_rows_per_page');

		if (!$perPage)
		{
			if ($globalPerPage && is_int($globalPerPage))
			{
				$perPage = $globalPerPage;
			}
			else
			{
				$perPage = $this->rowsPerPage;
			}
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