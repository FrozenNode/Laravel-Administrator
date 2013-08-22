<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns;

use Mockery as m;

class ColumnTest extends \PHPUnit_Framework_TestCase {

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

		$options = array('column_name' => 'test');
		$this->column = m::mock('Frozennode\Administrator\DataTable\Columns\Column', array($this->validator, $this->config, $this->db, $options))
						->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testValidates()
	{
		$this->column->shouldReceive('getRules')->once()->andReturn(array());
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(false);
		$this->column->validateOptions();
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testValidateFails()
	{
		$this->column->shouldReceive('getRules')->once()->andReturn(array());
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(true)
						->shouldReceive('messages')->once()->andReturn(m::mock(array('all' => array())));
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->column->validateOptions();
	}

	public function testBuild()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array()));
		$this->db->shouldReceive('getTablePrefix')->once()->andReturn('');
		$this->validator->shouldReceive('arrayGet')->times(4);
		$this->column->build();
	}

	public function testFilterQueryAddsSelect()
	{
		$this->column->shouldReceive('getOption')->twice()->andReturn('foo');
		$grammar = m::mock('Illuminate\Database\Query\Grammars');
		$grammar->shouldReceive('wrap')->once()->andReturn('');
		$this->db->shouldReceive('raw')->once()->andReturn('foo')
					->shouldReceive('getQueryGrammar')->once()->andReturn($grammar);
		$selects = array();
		$this->column->filterQuery($selects);
		$this->assertEquals($selects, array('foo'));
	}

	public function testFilterQueryDoesntAddSelect()
	{
		$this->column->shouldReceive('getOption')->once();
		$this->db->shouldReceive('raw')->never()
					->shouldReceive('getQueryGrammar')->never();
		$selects = array();
		$this->column->filterQuery($selects);
		$this->assertEquals($selects, array());
	}

	public function testGetOptions()
	{
		$this->column->shouldReceive('validateOptions')->once()
					->shouldReceive('build')->once()
					->shouldReceive('getDefaults')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->column->getOptions(), array('foo' => 'bar', 'column_name' => 'test'));
	}

	public function testGetOptionSucceeds()
	{
		$this->column->shouldReceive('getOptions')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->column->getOption('foo'), 'bar');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetOptionFails()
	{
		$this->column->shouldReceive('getOptions')->once()->andReturn(array('column_name' => 'bar'));
		$this->column->getOption('foo');
	}

}