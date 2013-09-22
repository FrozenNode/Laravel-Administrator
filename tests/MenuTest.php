<?php
namespace Frozennode\Administrator\Tests;

use Mockery as m;

class MenuTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The Config Repository mock
	 *
	 * @var Mockery
	 */
	protected $config;

	/**
	 * The ConfigFactory mock
	 *
	 * @var Mockery
	 */
	protected $configFactory;

	/**
	 * The Menu mock
	 *
	 * @var Mockery
	 */
	protected $menu;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->config = m::mock('Illuminate\Config\Repository');
		$this->configFactory = m::mock('Frozennode\Administrator\Config\Factory');
		$this->menu = m::mock('Frozennode\Administrator\Menu', array($this->config, $this->configFactory))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testGetMenuSimpleReturnWithPermission()
	{
		$this->config->shouldReceive('get')->once()->andReturn(array('test_name'));
		$itemconfig = m::mock('Frozennode\Administrator\Config\Config');
		$itemconfig->shouldReceive('getOption')->twice()->andReturn(true, 'test_title');
		$this->configFactory->shouldReceive('make')->once()->andReturn($itemconfig);
		$this->assertEquals($this->menu->getMenu(), array('test_name' => 'test_title'));
	}

	public function testGetMenuSimpleReturnWithoutPermission()
	{
		$this->config->shouldReceive('get')->once()->andReturn(array('test_name'));
		$itemconfig = m::mock('Frozennode\Administrator\Config\Config');
		$itemconfig->shouldReceive('getOption')->once()->andReturn(false);
		$this->configFactory->shouldReceive('make')->once()->andReturn($itemconfig);
		$this->assertEquals($this->menu->getMenu(), array());
	}

	public function testGetMenuNested()
	{
		$this->config->shouldReceive('get')->once()->andReturn(array('Header' => array('test_name')));
		$itemconfig = m::mock('Frozennode\Administrator\Config\Config');
		$itemconfig->shouldReceive('getOption')->twice()->andReturn(true, 'test_title');
		$this->configFactory->shouldReceive('make')->once()->andReturn($itemconfig);
		$this->assertEquals($this->menu->getMenu(), array('Header' => array('test_name' => 'test_title')));
	}

	public function testGetMenuNestedWithoutPermission()
	{
		$this->config->shouldReceive('get')->once()->andReturn(array('Header' => array('test_name')));
		$itemconfig = m::mock('Frozennode\Administrator\Config\Config');
		$itemconfig->shouldReceive('getOption')->once()->andReturn(false);
		$this->configFactory->shouldReceive('make')->once()->andReturn($itemconfig);
		$this->assertEquals($this->menu->getMenu(), array());
	}

	public function testGetMenuDeepNested()
	{
		$this->config->shouldReceive('get')->once()->andReturn(array('Header' => array('Header2' => array('test_name'))));
		$itemconfig = m::mock('Frozennode\Administrator\Config\Config');
		$itemconfig->shouldReceive('getOption')->twice()->andReturn(true, 'test_title');
		$this->configFactory->shouldReceive('make')->once()->andReturn($itemconfig);
		$this->assertEquals($this->menu->getMenu(), array('Header' => array('Header2' => array('test_name' => 'test_title'))));
	}

	public function testGetMenuDeepNestedWithoutPermission()
	{
		$this->config->shouldReceive('get')->once()->andReturn(array('Header' => array('Header2' => array('test_name'))));
		$itemconfig = m::mock('Frozennode\Administrator\Config\Config');
		$itemconfig->shouldReceive('getOption')->once()->andReturn(false);
		$this->configFactory->shouldReceive('make')->once()->andReturn($itemconfig);
		$this->assertEquals($this->menu->getMenu(), array());
	}

}