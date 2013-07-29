<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns;

use Mockery as m;

class ActionFactoryTest extends \PHPUnit_Framework_TestCase {

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
	 * The ColumnFactory mock
	 *
	 * @var Mockery
	 */
	protected $factory;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$this->factory = m::mock('Frozennode\Administrator\Actions\Factory', array($this->validator, $this->config))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testMake()
	{
		$this->factory->shouldReceive('parseDefaults')->once()->andReturn(array())
						->shouldReceive('getActionObject')->once()->andReturn('test');
		$this->assertEquals($this->factory->make('action', array()), 'test');
	}

	public function testParseDefaults()
	{
		$this->config->shouldReceive('getDataModel')->once();
		$this->validator->shouldReceive('arrayGet')->twice();
		$output = array('action_name' => 'action', 'has_permission' => true, 'messages' => array());
		$this->assertEquals($this->factory->parseDefaults('action', array()), $output);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseDefaultsInvalidName()
	{
		$this->config->shouldReceive('getDataModel')->once()
						->shouldReceive('getOption')->once();
		$this->factory->parseDefaults(true, array());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseDefaultsInvalidOptions()
	{
		$this->config->shouldReceive('getDataModel')->once()
						->shouldReceive('getOption')->once();
		$this->factory->parseDefaults('action', true);
	}

	public function testParseDefaultsPermissionFails()
	{
		$this->config->shouldReceive('getDataModel')->once();
		$this->validator->shouldReceive('arrayGet')->twice()->andReturn(function($model) {return false;});
		$result = $this->factory->parseDefaults('action', array());
		$this->assertEquals($result['has_permission'], false);
	}

	public function testGetByNameSucceeds()
	{
		$action = m::mock('Frozennode\Administrator\Actions\Action');
		$action->shouldReceive('getOption')->once()->andReturn('action');
		$this->factory->shouldReceive('getActions')->once()->andReturn(array($action));
		$this->assertEquals($this->factory->getByName('action'), $action);
	}

	public function testGetByNameFails()
	{
		$action = m::mock('Frozennode\Administrator\Actions\Action');
		$action->shouldReceive('getOption')->once()->andReturn('foo');
		$this->factory->shouldReceive('getActions')->once()->andReturn(array($action));
		$this->assertEquals($this->factory->getByName('action'), false);
	}

	public function testGetByNameGlobalSucceeds()
	{
		$action = m::mock('Frozennode\Administrator\Actions\Action');
		$action->shouldReceive('getOption')->once()->andReturn('action');
		$this->factory->shouldReceive('getGlobalActions')->once()->andReturn(array($action));
		$this->assertEquals($this->factory->getByName('action', true), $action);
	}

	public function testGetByNameGlobalFails()
	{
		$action = m::mock('Frozennode\Administrator\Actions\Action');
		$action->shouldReceive('getOption')->once()->andReturn('foo');
		$this->factory->shouldReceive('getGlobalActions')->once()->andReturn(array($action));
		$this->assertEquals($this->factory->getByName('action', true), false);
	}

	public function testGetActions()
	{
		$this->factory->shouldReceive('make')->times(3)->andReturn(1);
		$this->config->shouldReceive('getOption')->andReturn(array(array(), array(), array()));
		$this->assertEquals($this->factory->getActions(), array(1, 1, 1));
	}

	public function testGetActionsOptions()
	{
		$action = m::mock('Frozennode\Administrator\Actions\Action');
		$action->shouldReceive('getOptions')->times(3)->andReturn(1);
		$this->factory->shouldReceive('getActions')->andReturn(array($action, $action, $action));
		$this->assertEquals($this->factory->getActionsOptions(), array(1, 1, 1));
	}

	public function testGetGlobalActions()
	{
		$this->factory->shouldReceive('make')->times(3)->andReturn(1);
		$this->config->shouldReceive('getOption')->andReturn(array(array(), array(), array()));
		$this->assertEquals($this->factory->getGlobalActions(), array(1, 1, 1));
	}

	public function testGetGlobalActionsOptions()
	{
		$action = m::mock('Frozennode\Administrator\Actions\Action');
		$action->shouldReceive('getOptions')->times(3)->andReturn(1);
		$this->factory->shouldReceive('getGlobalActions')->andReturn(array($action, $action, $action));
		$this->assertEquals($this->factory->getGlobalActionsOptions(), array(1, 1, 1));
	}

	public function testGetActionPermissionsCallback()
	{
		$this->config->shouldReceive('getDataModel')->once()
						->shouldReceive('getOption')->once()->andReturn(array('foo' => function($model) {return false;}));
		$output = array('create' => true, 'view' => true, 'delete' => true, 'update' => true, 'foo' => false);
		$this->assertEquals($this->factory->getActionPermissions(), $output);
	}

	public function testGetActionPermissionsBool()
	{
		$this->config->shouldReceive('getDataModel')->once()
						->shouldReceive('getOption')->once()->andReturn(array('foo' => false));
		$output = array('create' => true, 'view' => true, 'delete' => true, 'update' => true, 'foo' => false);
		$this->assertEquals($this->factory->getActionPermissions(), $output);
	}
}