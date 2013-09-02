<?php
namespace Frozennode\Administrator\Tests\Fields;

use Mockery as m;

class BoolTest extends \PHPUnit_Framework_TestCase {

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
		$options = array('field_name' => 'field', 'type' => 'bool');
		$this->field = m::mock('Frozennode\Administrator\Fields\Bool', array($this->validator, $this->config, $this->db, $options))->makePartial();
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
		$this->validator->shouldReceive('arrayGet')->times(4);
		$this->field->build();
	}

	public function testFillModelTrue()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->once()->andReturn('field');
		$this->field->fillModel($model, 'true');
		$this->assertEquals($model->field, 1);
	}

	public function testFillModelFalse()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->once()->andReturn('field');
		$this->field->fillModel($model, 'false');
		$this->assertEquals($model->field, 0);
	}

	public function testSetFilter()
	{
		$this->validator->shouldReceive('arrayGet')->times(4);
		$this->field->shouldReceive('getFilterValue')->times(3)
					->shouldReceive('getOption')->times(3);
		$this->field->setFilter(null);
	}

	public function testFilterQueryWithValue()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->once();
		$this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getTable' => 'table')));
		$this->field->shouldReceive('getOption')->times(3)->andReturn('test');
		$this->field->filterQuery($query);
	}

	public function testFilterQueryNoValue()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->never();
		$this->field->shouldReceive('getOption')->once()->andReturn('');
		$this->field->filterQuery($query);
	}
}