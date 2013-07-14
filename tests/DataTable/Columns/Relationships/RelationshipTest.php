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

}