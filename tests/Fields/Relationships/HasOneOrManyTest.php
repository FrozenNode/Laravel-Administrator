<?php
namespace Frozennode\Administrator\Tests\Fields\Relationships;

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
		$options = array('field_name' => 'field', 'type' => 'has_one');
		$this->field = m::mock('Frozennode\Administrator\Fields\Relationships\HasOneOrMany',
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
		$relatedModel = m::mock(array('getKeyName' => 'id', 'getTable' => 'other_table'));
		$relationship = m::mock(array('getRelated' => $relatedModel, 'getForeignKey' => 'some_id', 'getPlainForeignKey' => 'some_other_id'));
		$model = m::mock(array('field' => $relationship, 'getTable' => 'table'));
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->times(6);
		$this->field->shouldReceive('setUpConstraints')->once()
					->shouldReceive('loadRelationshipOptions')->once();
		$this->field->build();
	}

	public function testConstrainQuery()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('where')->once();
		$this->field->shouldReceive('getOption')->once();
		$this->field->constrainQuery($query, m::mock(array()), 'foo');
	}
}