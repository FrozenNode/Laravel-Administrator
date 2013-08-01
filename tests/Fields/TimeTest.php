<?php
namespace Frozennode\Administrator\Tests\Fields;

use Mockery as m;

class TimeTest extends \PHPUnit_Framework_TestCase {

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
		$options = array('field_name' => 'field', 'type' => 'time');
		$this->field = m::mock('Frozennode\Administrator\Fields\Time', array($this->validator, $this->config, $this->db, $options))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFilterQueryWithMinAndMax()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->twice();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(4)->andReturn('3/3/2013')
					->shouldReceive('getDateString')->twice();
		$this->field->filterQuery($query);
	}

	public function testFilterQueryOnlyMin()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->once();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(3)->andReturn('3/3/2013', '', false)
					->shouldReceive('getDateString')->once();
		$this->field->filterQuery($query);
	}

	public function testFilterQueryOnlyMax()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->once();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(3)->andReturn(false, '3/3/2013')
					->shouldReceive('getDateString')->once();
		$this->field->filterQuery($query);
	}

	public function testFilterQueryNoMinOrMax()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->never();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->twice()->andReturn(false)
					->shouldReceive('getDateString')->never();
		$this->field->filterQuery($query);
	}

	public function testFillModel()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->once()->andReturn('field')
					->shouldReceive('getDateString')->once()->andReturn('flerp');
		$this->field->fillModel($model, '3/3/2013');
		$this->assertEquals($model->field, 'flerp');
	}

	public function testFillModelBadInput()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->never()
					->shouldReceive('getDateString')->never();
		$this->field->fillModel($model, null);
		$this->assertTrue(!isset($model->field));
	}

	public function testGetDateStringParsesDate()
	{
		$this->field->shouldReceive('getOption')->once()->andReturn('date');
		$this->assertEquals($this->field->getDateString(strtotime('3/3/2013')), '2013-03-03');
	}

	public function testGetDateStringParsesDateTime()
	{
		$this->field->shouldReceive('getOption')->twice()->andReturn('datetime');
		$this->assertEquals($this->field->getDateString(strtotime('3/3/2013 4:45pm')), '2013-03-03 16:45:00');
	}

	public function testGetDateStringParsesTime()
	{
		$this->field->shouldReceive('getOption')->twice()->andReturn('time');
		$this->assertEquals($this->field->getDateString(strtotime('4:45pm')), '16:45:00');
	}

}