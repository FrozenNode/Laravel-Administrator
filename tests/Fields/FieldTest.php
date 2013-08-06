<?php
namespace Frozennode\Administrator\Tests\Fields;

use Mockery as m;

class FieldTest extends \PHPUnit_Framework_TestCase {

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
		$options = array('field_name' => 'field', 'type' => 'text');
		$this->field = m::mock('Frozennode\Administrator\Fields\Field', array($this->validator, $this->config, $this->db, $options))->makePartial();
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
		$this->validator->shouldReceive('arrayGet')->times(3);
		$this->field->build();
	}

	public function testBuildRunsVisibleCheck()
	{
		$this->validator->shouldReceive('arrayGet')->times(3)->andReturn(null, function($param) {}, null);
		$this->config->shouldReceive('getDataModel')->once()->andReturn('test');
		$this->field->build();
	}

	public function testBuildRunsEditableCheck()
	{
		$this->validator->shouldReceive('arrayGet')->times(3)->andReturn(null, null, function($param) {});
		$this->config->shouldReceive('getDataModel')->once()->andReturn('test');
		$this->field->build();
	}

	public function testValidates()
	{
		$this->field->shouldReceive('getRules')->once()->andReturn(array());
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(false);
		$this->field->validateOptions();
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testValidateFails()
	{
		$this->field->shouldReceive('getRules')->once()->andReturn(array());
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(true)
						->shouldReceive('messages')->once()->andReturn(m::mock(array('all' => array())));
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->field->validateOptions();
	}

	public function testFillModelNullInput()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->once()->andReturn('field');
		$this->field->fillModel($model, null);
		$this->assertEquals($model->field, '');
	}

	public function testFillModelNotNullInput()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->once()->andReturn('field');
		$this->field->fillModel($model, 'test');
		$this->assertEquals($model->field, 'test');
	}

	public function testSetFilter()
	{
		$this->validator->shouldReceive('arrayGet')->times(3);
		$this->field->shouldReceive('getFilterValue')->times(3)
					->shouldReceive('getOption')->times(3);
		$this->field->setFilter(null);
	}

	public function testFilterQueryWithMinAndMax()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->twice();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(5)->andReturn('test');
		$this->field->filterQuery($query);
	}

	public function testFilterQueryOnlyMin()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->once();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(4)->andReturn('test', 'test', 'test' , false);
		$this->field->filterQuery($query);
	}

	public function testFilterQueryOnlyMax()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->once();
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(4)->andReturn('test', false, 'test' , 'test');
		$this->field->filterQuery($query);
	}

	public function testFilterQueryNoMinOrMax()
	{
		$model = m::mock(array('getTable' => 'table'));
		$query = m::mock('Illuminate\Database\Query\Builder');
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->once()->andReturn(false);
		$this->field->filterQuery($query);
	}

	public function testFilterValueReturnsValue()
	{
		$value = 'test';
		$this->assertEquals($this->field->getFilterValue($value), $value);
	}

	public function testFilterValueReturnsFalse()
	{
		$value = null;
		$this->assertEquals($this->field->getFilterValue($value), false);
	}

	public function testGetOptions()
	{
		$this->field->shouldReceive('validateOptions')->once()
					->shouldReceive('build')->once()
					->shouldReceive('getDefaults')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->field->getOptions(), array('foo' => 'bar', 'field_name' => 'field', 'type' => 'text'));
	}

	public function testGetOptionSucceeds()
	{
		$this->field->shouldReceive('getOptions')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->field->getOption('foo'), 'bar');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetOptionFails()
	{
		$this->field->shouldReceive('getOptions')->once()->andReturn(array('field_name' => 'bar'));
		$this->field->getOption('foo');
	}
}