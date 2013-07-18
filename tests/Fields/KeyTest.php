<?php
namespace Frozennode\Administrator\Tests\Fields;

use Mockery as m;

class KeyTest extends \PHPUnit_Framework_TestCase {

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
	 * The FieldFactory mock
	 *
	 * @var Mockery
	 */
	protected $field;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$this->db = m::mock('Illuminate\Database\DatabaseManager');
		$options = array('field_name' => 'field', 'type' => 'key');
		$this->field = m::mock('Frozennode\Administrator\Fields\Key', array($this->validator, $this->config, $this->db, $options))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFilterQueryWithValue()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->once();
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(4)->andReturn(false, true);
		$this->field->filterQuery($query);
	}

	public function testFilterQueryWithoutValue()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->never();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(2)->andReturn(false);
		$this->field->filterQuery($query);
	}

}