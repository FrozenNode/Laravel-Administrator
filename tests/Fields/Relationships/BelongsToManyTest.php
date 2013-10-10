<?php
namespace Frozennode\Administrator\Tests\Fields\Relationships;

use Mockery as m;

class BelongsToManyEloquentStub {
	public $fieldSort = '3,4,5';
	public $fieldNoSort = '3,4,5';
	public function fieldSort() {
		$relationship = m::mock('Illuminate\Database\Eloquent\Relations\BelongsToMany');
		$relationship->shouldReceive('detach')->once()
						->shouldReceive('attach')->times(3);
		return $relationship;
	}
	public function fieldNoSort() {
		$relationship = m::mock('Illuminate\Database\Eloquent\Relations\BelongsToMany');
		$relationship->shouldReceive('sync')->once();
		return $relationship;
	}
	public function __unset($rel) {unset($this->{$rel});}
}

class BelongsToManyTest extends \PHPUnit_Framework_TestCase {

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
		$options = array('field_name' => 'field', 'type' => 'belongs_to_many');
		$this->field = m::mock('Frozennode\Administrator\Fields\Relationships\BelongsToMany',
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
		$relationship = m::mock(array('getRelated' => $relatedModel, 'getForeignKey' => 'some_id', 'getOtherKey' => 'some_other_id',
										'getTable' => 'table'));
		$model = m::mock(array('field' => $relationship, 'getTable' => 'table'));
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->times(6);
		$this->field->shouldReceive('setUpConstraints')->once()
					->shouldReceive('loadRelationshipOptions')->once();
		$this->field->build();
	}

	public function testFillModelWithSortField()
	{
		$model = new BelongsToManyEloquentStub;
		$this->field->shouldReceive('getOption')->twice()->andReturn('fieldSort', 'sort');
		$this->field->fillModel($model, '3,4,5');
		$this->assertTrue(!isset($model->rel));
	}

	public function testFillModelWithoutSortField()
	{
		$model = new BelongsToManyEloquentStub;
		$this->field->shouldReceive('getOption')->twice()->andReturn('fieldNoSort', false);
		$this->field->fillModel($model, '3,4,5');
		$this->assertTrue(!isset($model->rel));
	}

	public function testFilterQueryWithValueNotJoined()
	{
		$connection = m::mock('Illuminate\Database\Connection');
		$connection->shouldReceive('getTablePrefix')->once()->andReturn('');
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('whereIn')->once()
				->shouldReceive('join')->once()
				->shouldReceive('havingRaw')->once()
				->shouldReceive('getConnection')->once()->andReturn($connection);
		$this->validator->shouldReceive('isJoined')->once()->andReturn(false);
		$model = m::mock(array('getTable' => 'table', 'getKeyName' => 'id'));
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(5)->andReturn(false, 'test');
		$selects = array();
		$this->field->filterQuery($query, $selects);
	}

	public function testFilterQueryWithValueAlreadyJoined()
	{
		$connection = m::mock('Illuminate\Database\Connection');
		$connection->shouldReceive('getTablePrefix')->once()->andReturn('');
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('whereIn')->once()
				->shouldReceive('join')->never()
				->shouldReceive('havingRaw')->once()
				->shouldReceive('getConnection')->once()->andReturn($connection);
		$this->validator->shouldReceive('isJoined')->once()->andReturn(true);
		$model = m::mock(array('getTable' => 'table', 'getKeyName' => 'id'));
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(5)->andReturn(false, 'test');
		$selects = array();
		$this->field->filterQuery($query, $selects);
	}

	public function testFilterQueryWithoutValue()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('whereIn')->never()
				->shouldReceive('getConnection')->never();
		$this->validator->shouldReceive('isJoined')->never();
		$model = m::mock(array('getTable' => 'table', 'getKeyName' => 'id'));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->field->shouldReceive('getOption')->times(5)->andReturn(false);
		$selects = array();
		$this->field->filterQuery($query, $selects);
	}

	public function testConstrainQueryNotJoined()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('join')->once()
				->shouldReceive('where')->once();
		$this->validator->shouldReceive('isJoined')->once();
		$model = m::mock(array('getTable' => 'table', 'getKeyName' => 'id'));
		$this->field->shouldReceive('getOption')->times(4);
		$this->field->constrainQuery($query, $model, 'foo');
	}

	public function testConstrainQueryAlreadyJoined()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('join')->never()
				->shouldReceive('where')->once();
		$this->validator->shouldReceive('isJoined')->once()->andReturn(true);
		$model = m::mock(array('getTable' => 'table', 'getKeyName' => 'id'));
		$this->field->shouldReceive('getOption')->twice();
		$this->field->constrainQuery($query, $model, 'foo');
	}

}
