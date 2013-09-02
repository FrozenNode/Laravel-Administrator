<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns\Relationships;

use Mockery as m;

class RelationshipTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The Validator mock
	 *
	 * @var Mockery
	 */
	protected $validator;

	/**
	 * The Config mock
	 *
	 * @var Mockery
	 */
	protected $config;

	/**
	 * The DB mock
	 *
	 * @var Mockery
	 */
	protected $db;

	/**
	 * The Column mock
	 *
	 * @var Mockery
	 */
	protected $column;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$this->db = m::mock('Illuminate\Database\DatabaseManager');

		$options = array('column_name' => 'test', 'relationship' => 'method', 'select' => 'foo');
		$this->column = m::mock('Frozennode\Administrator\DataTable\Columns\Relationships\Relationship',
											array($this->validator, $this->config, $this->db, $options))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testBuild()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('method' => m::mock(array('getRelated' => m::mock(array('getTable' => '')))))));
		$this->db->shouldReceive('getTablePrefix')->once()->andReturn('');
		$this->column->build();
	}

	public function testGetIncludedColumnReturnsEmptyArray()
	{
		$this->assertEquals($this->column->getIncludedColumn(), array());
	}

	public function testGetRelationshipWheres()
	{
		$connection = m::mock('Illuminate\Database\Connection');
		$connection->shouldReceive('prepareBindings')->once()->andReturn(array());
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('getConnection')->once()->andReturn($connection)
				->shouldReceive('getBindings')->once()->andReturn(array())
				->shouldReceive('toSql')->once()->andReturn('');
		$query->wheres = array(array(), array('column' => 'bar'));
		$eloquentQuery = m::mock('Illuminate\Database\Eloquent\Builder');
		$eloquentQuery->shouldReceive('getQuery')->once()->andReturn($query);
		$relatedModel = m::mock('Illuminate\Database\Eloquent\Model');
		$relatedModel->shouldReceive('isSoftDeleting')->once();
		$relationship = m::mock('Illuminate\Database\Eloquent\Relations\Relation');
		$relationship->shouldReceive('getQuery')->once()->andReturn($eloquentQuery)
						->shouldReceive('getRelated')->once()->andReturn($relatedModel);
		$this->column->shouldReceive('interpolateQuery')->once()->andReturn('foo where test')
					->shouldReceive('aliasRelationshipWhere')->once()->andReturn('foo');
		$result = $this->column->getRelationshipWheres($relationship, 'fooalias');
		$this->assertEquals($result, 'test');
	}

	public function testAliasRelationshipWhereUnaliasedColumnOtherTable()
	{
		$result = $this->column->aliasRelationshipWhere('column', 'table_alias', 'pivot_alias', 'pivot');
		$this->assertEquals($result, 'table_alias.column');
	}

	public function testAliasRelationshipWhereAliasedColumnOtherTable()
	{
		$result = $this->column->aliasRelationshipWhere('table.column', 'table_alias', 'pivot_alias', 'pivot');
		$this->assertEquals($result, 'table_alias.column');
	}

	public function testAliasRelationshipWhereAliasedColumnPivotTable()
	{
		$result = $this->column->aliasRelationshipWhere('pivot.column', 'table_alias', 'pivot_alias', 'pivot');
		$this->assertEquals($result, 'pivot_alias.column');
	}

	public function testInterpolateQuery()
	{
		$query = "select herp from derp where foo = ? AND bar = ? AND num = ? AND IN ?";
		$params = array(null, 'test', 5, array(1, 2));
		$result = $this->column->interpolateQuery($query, $params);
		$expectedQuery = "select herp from derp where foo = NULL AND bar = 'test' AND num = 5 AND IN 1,2";
		$this->assertEquals($result, $expectedQuery);
	}

}