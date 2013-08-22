<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns;

use Mockery as m;

class EloquentStub {
	public function bt() {return m::mock('Illuminate\Database\Eloquent\Relations\BelongsTo');}
	public function btm() {return m::mock('Illuminate\Database\Eloquent\Relations\BelongsToMany');}
	public function hm() {return m::mock('Illuminate\Database\Eloquent\Relations\HasMany');}
	public function ho() {return m::mock('Illuminate\Database\Eloquent\Relations\HasOne');}
}

class ColumnStub {
	public $foo = 'bar';
}

class ColumnFactoryTest extends \PHPUnit_Framework_TestCase {

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
	 * The ColumnFactory mock
	 *
	 * @var Mockery
	 */
	protected $factory;

	/**
	 * The namespace prefix that we have to use in order to get around the weird php quirk that requires you to specify the
	 * fully qualified class name if you do "new $class".
	 *
	 * @var string
	 */
	protected $namespace = 'Frozennode\\Administrator\\DataTable\\Columns\\';

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$this->db = m::mock('Illuminate\Database\DatabaseManager');
		$this->factory = m::mock('Frozennode\Administrator\DataTable\Columns\Factory', array($this->validator, $this->config, $this->db))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testMakeGetsColumnObject()
	{
		$this->factory->shouldReceive('getColumnObject')->once()->andReturn('test');
		$this->assertEquals($this->factory->make(array()), 'test');
	}

	public function testGetColumnObject()
	{
		$stub = new ColumnStub;
		$this->factory->shouldReceive('getColumnClassName')->once()->andReturn('Frozennode\Administrator\Tests\DataTable\Columns\ColumnStub');
		$otherStub = $this->factory->getColumnObject(array());
		$this->assertEquals($otherStub->foo, $stub->foo);
	}

	public function testGetColumnReturnsColumn()
	{
		$this->config->shouldReceive('getDataModel')->once();
		$this->validator->shouldReceive('arrayGet')->once()->andReturn(false);
		$this->assertEquals($this->factory->getColumnClassName(array()), $this->namespace . 'Column');
	}

	public function testGetColumnClassNameReturnsBelongsToWhenDottedString()
	{
		$model = new EloquentStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->once()->andReturn('bt');
		$this->assertEquals($this->factory->getColumnClassName(array()), $this->namespace . 'Relationships\\BelongsTo');
	}

	public function testGetColumnClassNameReturnsBelongsTo()
	{
		$model = new EloquentStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->once()->andReturn('bt.otherbt');
		$this->assertEquals($this->factory->getColumnClassName(array()), $this->namespace . 'Relationships\\BelongsTo');
	}

	public function testGetColumnClassNameReturnsHasOne()
	{
		$model = new EloquentStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->once()->andReturn('ho');
		$this->assertEquals($this->factory->getColumnClassName(array()), $this->namespace . 'Relationships\\HasOneOrMany');
	}

	public function testGetColumnClassNameReturnsHasMany()
	{
		$model = new EloquentStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->once()->andReturn('hm');
		$this->assertEquals($this->factory->getColumnClassName(array()), $this->namespace . 'Relationships\\HasOneOrMany');
	}

	public function testGetColumnClassNameReturnsBelongsToMany()
	{
		$model = new EloquentStub;
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->validator->shouldReceive('arrayGet')->once()->andReturn('btm');
		$this->assertEquals($this->factory->getColumnClassName(array()), $this->namespace . 'Relationships\\BelongsToMany');
	}

	public function testParseOptionsSimpleStringReturnsIndexedArray()
	{
		$this->assertEquals($this->factory->parseOptions(0, 'funky'), array('column_name' => 'funky'));
	}

	public function testParseOptionsWithOptionsReturnsIndexedArray()
	{
		$this->assertEquals($this->factory->parseOptions('funky', array('title' => 'Funky')), array('column_name' => 'funky', 'title' => 'Funky'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseOptionsInvalidValueThrowsError()
	{
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->parseOptions(0, null);
	}

	public function testGetColumnsReturnsIndexedArray()
	{
		$columnObject = m::mock(array('getOption' => 'foo'));
		$this->config->shouldReceive('getOption')->once()->andReturn(array('foo' => array()));
		$this->factory->shouldReceive('parseOptions')->once()->andReturn(array())
					->shouldReceive('make')->once()->andReturn($columnObject);
		$this->assertEquals($this->factory->getColumns(), array('foo' => $columnObject));
	}

	public function testGetColumnOptionsReturnsUnIndexedArray()
	{
		$this->factory->shouldReceive('getColumns')->once()->andReturn(array('foo' => m::mock(array('getOptions' => 'bar'))));
		$this->assertEquals($this->factory->getColumnOptions(), array('bar'));
	}

	public function testGetIncludedColumnsReturnsColumns()
	{
		$this->validator->shouldReceive('arrayGet')->once()->andReturn(true);
		$model = m::mock(array('getTable' => 'table', 'getKeyName' => 'normal'));
		$columnRelated = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$columnRelated->shouldReceive('getOption')->once()->andReturn(true)
						->shouldReceive('getIncludedColumn')->andReturn(array('related' => 'whatever'));
		$columnNormal = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$columnNormal->shouldReceive('getOption')->times(4)->andReturn(false, false, 'normal', 'normal');
		$columnComputed = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$columnComputed->shouldReceive('getOption')->twice()->andReturn(false, true);
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->factory->shouldReceive('getColumns')->once()->andReturn(array('related' => $columnRelated, 'normal' => $columnNormal, 'computed' => $columnComputed));
		$this->assertEquals($this->factory->getIncludedColumns(array()), array('related' => 'whatever', 'normal' => 'table.normal'));
	}

	public function testGetIncludedColumnsAddsPrimaryKey()
	{
		$this->validator->shouldReceive('arrayGet')->once()->andReturn(false);
		$model = m::mock('Illuminate\Database\ELoquent\Model');
		$model->shouldReceive('getTable')->once()->andReturn('table')
				->shouldReceive('getKeyName')->times(3)->andReturn('id');
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->factory->shouldReceive('getColumns')->once()->andReturn(array());
		$this->assertEquals($this->factory->getIncludedColumns(array()), array('id' => 'table.id'));
	}

	public function testGetIncludedColumnsAddsBelongsToField()
	{
		$this->validator->shouldReceive('arrayGet')->once()->andReturn(true);
		$model = m::mock('Illuminate\Database\ELoquent\Model');
		$model->shouldReceive('getTable')->once()->andReturn('table')
				->shouldReceive('getKeyName')->once()->andReturn('id');
		$field = m::mock('Frozennode\\Administrator\\Fields\\Relationships\\BelongsTo');
		$field->shouldReceive('getOption')->twice()->andReturn('bt_id');
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->factory->shouldReceive('getColumns')->once()->andReturn(array());
		$this->assertEquals($this->factory->getIncludedColumns(array($field)), array('bt_id' => 'table.bt_id'));
	}

	public function testGetRelatedColumns()
	{
		$columnRelated = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$columnRelated->shouldReceive('getOption')->times(3)->andReturn(true, 'related');
		$columnNormal = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$columnNormal->shouldReceive('getOption')->once()->andReturn(false);
		$this->factory->shouldReceive('getColumns')->once()->andReturn(array($columnRelated, $columnNormal));
		$this->assertEquals($this->factory->getRelatedColumns(), array('related' => 'related'));
	}

	public function testGetComputedColumns()
	{
		$columnRelated = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$columnRelated->shouldReceive('getOption')->once()->andReturn(true);
		$columnComputed = m::mock('Frozennode\Administrator\DataTable\Columns\Column');
		$columnComputed->shouldReceive('getOption')->times(4)->andReturn(false, true, 'computed');
		$this->factory->shouldReceive('getColumns')->once()->andReturn(array($columnRelated, $columnComputed));
		$this->assertEquals($this->factory->getComputedColumns(), array('computed' => 'computed'));
	}

}