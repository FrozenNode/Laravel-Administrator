<?php
namespace Frozennode\Administrator\Tests\Fields;

use Mockery as m;

class NumberTest extends \PHPUnit_Framework_TestCase {

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
		$options = array('field_name' => 'field', 'type' => 'number');
		$this->field = m::mock('Frozennode\Administrator\Fields\Number', array($this->validator, $this->config, $this->db, $options))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testSetFilter()
	{
		$this->validator->shouldReceive('arrayGet')->times(3);
		$this->field->shouldReceive('getFilterValue')->times(3)
					->shouldReceive('getOption')->times(5);
		$this->field->setFilter(null);
	}

	public function testFillModel()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->once()->andReturn('field')
					->shouldReceive('parseNumber')->once()->andReturn('flerp');
		$this->field->fillModel($model, 'test');
		$this->assertEquals($model->field, 'flerp');
	}

	public function testFillModelNullInput()
	{
		$model = new \stdClass();
		$this->field->shouldReceive('getOption')->once()->andReturn('field')
					->shouldReceive('parseNumber')->never();
		$this->field->fillModel($model, null);
		$this->assertEquals($model->field, null);
	}

	public function testParseNumber()
	{
		$this->field->shouldReceive('getOption')->times(2)->andReturn('[', ']');
		$this->assertEquals($this->field->parseNumber('1]454]432[23'), '1454432.23');
	}

}