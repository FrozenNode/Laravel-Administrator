<?php
namespace Frozennode\Administrator\Tests\Fields\Relationships;

use Mockery as m;

class EloquentStub {
	public function foo() {}
	public function bar() {}
}

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
		$options = array('field_name' => 'field', 'type' => 'relationship');
		$this->field = m::mock('Frozennode\Administrator\Fields\Relationships\Relationship',
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
		$relationship = m::mock(array('getRelated' => m::mock(array('getTable' => 'table'))));
		$model = m::mock(array('getTable' => 'table', 'field' => $relationship));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->times(6);
		$this->field->shouldReceive('setUpConstraints')->once()
					->shouldReceive('loadRelationshipOptions')->once();
		$this->field->build();
	}

	public function testSetUpConstraintsOneInvalidConstraint()
	{
		$constraints = array('foo' => 'relationship', 'funky' => 'relationship');
		$model = new EloquentStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->once()->andReturn($constraints);
		$options = array();
		$this->field->setUpConstraints($options);
		$this->assertEquals($options['constraints'], array('foo' => 'relationship'));
	}

	public function testSetUpConstraintsEmpty()
	{
		$constraints = array();
		$model = new EloquentStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->once()->andReturn($constraints);
		$options = array();
		$this->field->setUpConstraints($options);
		$this->assertTrue(!isset($options['constraints']));
	}

	public function testLoadRelationshipOptionsAll()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('get')->once()->andReturn(array());
		$relatedModel = m::mock('Illuminate\Database\Eloquent\Model');
		$relatedModel->shouldReceive('newQuery')->once()->andReturn($query)
					->shouldReceive('getKeyName')->once()->andReturn('id');
		$relationship = m::mock(array('getRelated' => $relatedModel));
		$model = m::mock(array('field' => $relationship));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->times(3)->andReturn(true, false);
		$options = array('field_name' => 'field', 'options_filter' => function() {});
		$this->field->shouldReceive('mapRelationshipOptions')->once()->andReturn(array('funky'));
		$this->field->loadRelationshipOptions($options);
		$this->assertEquals($options['options'], array('funky'));
	}

	public function testLoadRelationshipOptionsWithOptionsSortField()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('get')->once()->andReturn(array());
		$relatedModel = m::mock('Illuminate\Database\Eloquent\Model');
		$relatedModel->shouldReceive('orderBy')->once()->andReturn($query)
					->shouldReceive('getKeyName')->once()->andReturn('id');
		$relationship = m::mock(array('getRelated' => $relatedModel));
		$model = m::mock(array('field' => $relationship));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->times(4)->andReturn(true, true);
		$this->db->shouldReceive('raw')->once();
		$options = array('field_name' => 'field', 'options_filter' => function() {});
		$this->field->shouldReceive('mapRelationshipOptions')->once()->andReturn(array('funky'));
		$this->field->loadRelationshipOptions($options);
		$this->assertEquals($options['options'], array('funky'));
	}

	public function testLoadRelationshipOptionsSkipLoad()
	{
		$relatedModel = m::mock('Illuminate\Database\Eloquent\Model');
		$relatedModel->shouldReceive('getKeyName')->once()->andReturn('id');
		$relationship = m::mock(array('getRelated' => $relatedModel, 'get' => array()));
		$model = m::mock(array('field' => $relationship));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->twice()->andReturn(false);
		$options = array('field_name' => 'field');
		$this->field->shouldReceive('mapRelationshipOptions')->once()->andReturn(array('funky'));
		$this->field->loadRelationshipOptions($options);
		$this->assertEquals($options['options'], array('funky'));
	}

	public function testMapRelationshipOptions()
	{
		$item1 = new \stdClass();
		$item1->id = 1;
		$item1->name = 'first';
		$item2 = new \stdClass();
		$item2->id = 2;
		$item2->name = 'second';
		$items = array($item1, $item2);
		$output = array(array('id' => 1, 'text' => 'first'), array('id' => 2, 'text' => 'second'));
		$this->assertEquals($this->field->mapRelationshipOptions($items, 'name', 'id'), $output);
	}

}