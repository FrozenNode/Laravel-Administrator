<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns\Relationships;

use Mockery as m;

class BelongsToStub {
	public function bt() {
		$mock = m::mock('Illuminate\\Database\\Eloquent\\Relations\\BelongsTo');
		$mock->shouldReceive('getRelated')->zeroOrMoreTimes()->andReturn(new BelongsToNestStub);
		return $mock;
	}
}

class BelongsToNestStub {
	public function btnest() {
		$mock = m::mock('Illuminate\\Database\\Eloquent\\Relations\\BelongsTo');
		$mock->shouldReceive('getRelated')->zeroOrMoreTimes()->andReturn(new BelongsToDeepNestStub);
		return $mock;
	}
}

class BelongsToDeepNestStub {
	public function btdeepnest() {
		$mock = m::mock('Illuminate\\Database\\Eloquent\\Relations\\BelongsTo');
		$mock->shouldReceive('getRelated')->zeroOrMoreTimes()->andReturn(new BelongsToDeepNestStub);
		return $mock;
	}
}

class BelongsToTest extends \PHPUnit_Framework_TestCase {

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
		$this->column = m::mock('Frozennode\Administrator\DataTable\Columns\Relationships\BelongsTo',
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
		$relevantModel = m::mock(array('method' => m::mock(array('getRelated' => m::mock(array('getTable' => ''))))));
		$nested = array('pieces' => array('method'), 'models' => array($relevantModel, 'foo'));
		$this->column->shouldReceive('getNestedRelationships')->once()->andReturn($nested);
		$this->db->shouldReceive('getTablePrefix')->once()->andReturn('');
		$this->column->build();
	}

	public function testGetNestedRelationshipsSingle()
	{
		$name = 'bt';
		$stub = new BelongsToStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($stub);
		$nested = $this->column->getNestedRelationships($name);

		$this->assertEquals($nested['pieces'], array($name));
		$this->assertEquals($nested['models'][0], $stub);
		$this->assertEquals(sizeof($nested['models']), 2);
	}

	public function testGetNestedRelationshipsNest()
	{
		$name = 'bt.btnest';
		$stub = new BelongsToStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($stub);
		$nested = $this->column->getNestedRelationships($name);

		$this->assertEquals($nested['pieces'], array('bt', 'btnest'));
		$this->assertEquals($nested['models'][0], $stub);
		$this->assertEquals(sizeof($nested['models']), 3);
	}

	public function testGetNestedRelationshipsDeepNest()
	{
		$name = 'bt.btnest.btdeepnest';
		$stub = new BelongsToStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($stub);
		$nested = $this->column->getNestedRelationships($name);

		$this->assertEquals($nested['pieces'], array('bt', 'btnest', 'btdeepnest'));
		$this->assertEquals($nested['models'][0], $stub);
		$this->assertEquals(sizeof($nested['models']), 4);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetNestedRelationshipsFails()
	{
		$name = 'nope';
		$stub = new BelongsToStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($stub)
					->shouldReceive('getOption')->once()->andReturn('');
		$this->column->shouldReceive('getOption')->once()->andReturn('');
		$nested = $this->column->getNestedRelationships($name);
	}

	public function testGetIncludedColumn()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getTable' => 'table')));
		$nested = array('pieces' => array('foo'), 'models' => array(m::mock(array('foo' => m::mock(array('getForeignKey' => 'fk'))))));
		$this->column->shouldReceive('getOption')->once()->andReturn($nested);
		$this->assertEquals($this->column->getIncludedColumn(), array('fk' => 'table.fk'));
	}
}