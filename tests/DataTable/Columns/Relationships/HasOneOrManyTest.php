<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns\Relationships;

use Mockery as m;

class HasOneOrManyTest extends \PHPUnit_Framework_TestCase {

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
		$this->column = m::mock('Frozennode\Administrator\DataTable\Columns\Relationships\HasOneOrMany',
											array($this->validator, $this->config, $this->db, $options))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFilterQuery()
	{
		$relationship = m::mock(array('getPlainForeignKey' => '', 'getRelated' => m::mock(array('getTable' => 'table'))));
		$model = m::mock(array('getTable' => 'table', 'getKeyName' => '', 'method' => $relationship));
		$grammar = m::mock('Illuminate\Database\Query\Grammars');
		$grammar->shouldReceive('wrap')->once()->andReturn('');
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->column->shouldReceive('getOption')->times(3)->andReturn('column_name', 'method', 'select')
						->shouldReceive('getRelationshipWheres')->once()->andReturn('');
		$this->db->shouldReceive('raw')->once()->andReturn('foo')
					->shouldReceive('getQueryGrammar')->once()->andReturn($grammar);
		$selects = array();
		$this->column->filterQuery($selects);
		$this->assertEquals($selects, array('foo'));
	}

}