<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns;

use Mockery as m;

class DataTableTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The Config mock
	 *
	 * @var Mockery
	 */
	protected $config;

	/**
	 * The ColumnFactory mock
	 *
	 * @var Mockery
	 */
	protected $columnFactory;

	/**
	 * The FieldFactory mock
	 *
	 * @var Mockery
	 */
	protected $fieldFactory;

	/**
	 * The DataTable mock
	 *
	 * @var Mockery
	 */
	protected $dataTable;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$this->columnFactory = m::mock('Frozennode\Administrator\DataTable\Columns\Factory');
		$this->fieldFactory = m::mock('Frozennode\Administrator\Fields\Factory');
		$this->dataTable = m::mock('Frozennode\Administrator\DataTable\DataTable', array($this->config, $this->columnFactory, $this->fieldFactory))
								->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testGetRows()
	{
		$countQuery = m::mock('Illuminate\Database\Query\Builder');
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('take')->once()
				->shouldReceive('skip')->once()
				->shouldReceive('get')->once()->andReturn(array('foo'));
		$prepared = array(
			'query' => $query,
			'countQuery' => $countQuery,
			'querySql' => 'foo',
			'sort' => array('field' => 'foo', 'direction' => 'asc'),
			'selects' => array(),
		);
		$countResults = array('page' => 30, 'last' => 60, 'total' => 4000);
		$this->dataTable->shouldReceive('prepareQuery')->once()->andReturn($prepared)
						->shouldReceive('performCountQuery')->once()->andReturn($countResults)
						->shouldReceive('parseResults')->once()->andReturn(array('funky'));
		$db = m::mock('Illuminate\Database\DatabaseManager');
		$output = array_merge($countResults, array('results' => array('funky')));
		$this->assertEquals($this->dataTable->getRows($db), $output);
	}

	public function testPrepareQuery()
	{
		$column = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$column->shouldReceive('filterQuery')->once()
				->shouldReceive('getOption')->times(2);
		$columns = array($column);
		$this->columnFactory->shouldReceive('getColumns')->once()->andReturn($columns);
		$countQuery = m::mock('Illuminate\Database\Query\Builder');
		$connection = m::mock('Illuminate\Database\Connection');
		$connection->shouldReceive('table')->once()->andReturn(m::mock(array('groupBy' => $countQuery)));
		$dbQuery = m::mock('Illuminate\Database\Query\Builder');
		$dbQuery->shouldReceive('select')->twice()
				->shouldReceive('getConnection')->once()->andReturn($connection);
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('getQuery')->twice()->andReturn($dbQuery)
				->shouldReceive('toSql')->once()->andReturn('sql string')
				->shouldReceive('orderBy')->once()
				->shouldReceive('distinct')->once();
		$model = m::mock('Illuminate\Database\Eloquent\Model');
		$model->shouldReceive('getTable')->once()->andReturn('table')
				->shouldReceive('getKeyName')->once()->andReturn('id')
				->shouldReceive('groupBy')->once()->andReturn($query);
		$db = m::mock('Illuminate\Database\DatabaseManager');
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model)
						->shouldReceive('runQueryFilter')->twice();
		$this->dataTable->shouldReceive('setSort')->once()
						->shouldReceive('getSort')->once()->andReturn(array('field' => 'id', 'direction' => 'asc'))
						->shouldReceive('setFilters')->once();
		$output = array(
			'query' => $query,
			'querySql' => 'sql string',
			'countQuery' => $countQuery,
			'sort' => array('field' => 'table.id', 'direction' => 'asc'),
			'selects' => array('table.*'),
		);
		$this->assertEquals($this->dataTable->prepareQuery($db), $output);
	}

	public function testPerformCountQuery()
	{
		$result = new \stdClass;
		$result->aggregate = 100;
		$countQuery = m::mock('Illuminate\Database\Query\Builder');
		$countQuery->shouldReceive('getConnection')->once()->andReturn(m::mock(array('select' => array($result))))
					->shouldReceive('getBindings')->once();
		$model = m::mock('Illuminate\Database\Eloquent\Model');
		$model->shouldReceive('getKeyName')->once()->andReturn('id');
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$output = array('page' => 1, 'last' => 5, 'total' => 100);
		$this->assertEquals($this->dataTable->performCountQuery($countQuery, 'foo', 1), $output);
	}

	public function testSetFilters()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$countQuery = m::mock('Illuminate\Database\Query\Builder');
		$filters = array(array('field_name' => 1), array('field_name' => 2), array('field_name' => 3));
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('setFilter')->times(3)
				->shouldReceive('filterQuery')->times(6);
		$this->fieldFactory->shouldReceive('findFilter')->times(3)->andReturn($field);
		$selects = array();
		$this->dataTable->setFilters($filters, $query, $countQuery, $selects);
	}

	public function testParseResults()
	{
		$this->dataTable->shouldReceive('parseOnTableColumns')->times(3)
						->shouldReceive('parseComputedColumns')->times(3);
		$this->assertEquals($this->dataTable->parseResults(array(1, 2, 3)), array(array(), array(), array()));
	}

	public function testParseOnTableColumns()
	{
		$column1 = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$column1->shouldReceive('renderOutput')->once()->andReturn('rendered');
		$column2 = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$column2->shouldReceive('renderOutput')->once()->andReturn('rendered');
		$columns = array('column1' => $column1, 'column2' => $column2);
		$this->columnFactory->shouldReceive('getColumns')->once()->andReturn($columns);
		$this->columnFactory->shouldReceive('getIncludedColumns')->once()->andReturn(array('column1' => 'foo', 'key_column' => 'flerp'));
		$this->columnFactory->shouldReceive('getRelatedColumns')->once()->andReturn(array('column2' => 'bar'));
		$this->fieldFactory->shouldReceive('getEditFields')->once()->andReturn(array());
		$model = m::mock('Illuminate\Database\Eloquent\Model');
		$model->shouldReceive('getAttribute')->times(3)->andReturn('raw');
		$outputRow = array();
		$regColumnOutput = array('raw'=>'raw','rendered'=>'rendered');
		$testOutput = array('column1' => $regColumnOutput, 'column2' => $regColumnOutput, 'key_column' => array('raw'=>'raw','rendered'=>'raw'));
		$this->dataTable->parseOnTableColumns($model, $outputRow);
		$this->assertEquals($outputRow, $testOutput);
	}

	public function testParseComputedColumns()
	{
		$column = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$column->shouldReceive('renderOutput')->twice()->andReturn('rendered');
		$columns = array('column1' => $column, 'column2' => $column);
		$this->columnFactory->shouldReceive('getColumns')->once()->andReturn($columns);
		$this->columnFactory->shouldReceive('getComputedColumns')->once()->andReturn(array('column1' => 'foo', 'column2' => 'flerp'));
		$model = new \stdClass;
		$model->column1 = 'raw';
		$model->column2 = 'raw';
		$outputRow = array();
		$regColumnOutput = array('raw'=>'raw','rendered'=>'rendered');
		$testOutput = array('column1' => $regColumnOutput, 'column2' => $regColumnOutput);
		$this->dataTable->parseComputedColumns($model, $outputRow);
		$this->assertEquals($outputRow, $testOutput);
	}

	public function testSetSortNullNoDefaults()
	{
		$this->config->shouldReceive('getOption')->once()->andReturn(array())
						->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getKeyName' => 'id')));
		$this->dataTable->setSort();
		$this->assertEquals($this->dataTable->getSort(), array('field' => 'id', 'direction' => 'desc'));
	}

	public function testSetSortNullWithDefaults()
	{
		$this->config->shouldReceive('getOption')->once()->andReturn(array('field' => 'foo', 'direction' => 'bar'))
						->shouldReceive('getDataModel')->never();
		$this->dataTable->setSort();
		$this->assertEquals($this->dataTable->getSort(), array('field' => 'foo', 'direction' => 'desc'));
	}

	public function testSetSortWithInput()
	{
		$this->config->shouldReceive('getOption')->never()
						->shouldReceive('getDataModel')->never();
		$this->dataTable->setSort(array('field' => 'foo', 'direction' => 'bar'));
		$this->assertEquals($this->dataTable->getSort(), array('field' => 'foo', 'direction' => 'desc'));
	}

	public function testSetRowsPerPageDefaultsToGlobal()
	{
		$session = m::mock('Illuminate\Session\Store');
		$session->shouldReceive('get')->once();
		$this->config->shouldReceive('getOption')->once();
		$this->dataTable->setRowsPerPage($session, 54);
		$this->assertEquals($this->dataTable->getRowsPerPage(), 54);
	}

	public function testSetRowsPerPageDefaultsToUserChoice()
	{
		$session = m::mock('Illuminate\Session\Store');
		$session->shouldReceive('get')->once()->andReturn(23);
		$this->config->shouldReceive('getOption')->once();
		$this->dataTable->setRowsPerPage($session, 54);
		$this->assertEquals($this->dataTable->getRowsPerPage(), 23);
	}
}