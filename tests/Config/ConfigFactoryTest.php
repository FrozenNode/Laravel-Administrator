<?php
namespace Frozennode\Administrator\Tests\Config;

use Mockery as m;
use Frozennode\Administrator\Config\Factory;

class ConfigFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The Validator mock
	 *
	 * @var Mockery
	 */
	protected $validator;

	/**
	 * The default config
	 *
	 * @var array
	 */
	protected $config = array(
		'menu' => array('foo'),
	);

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(false)->byDefault();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Tests that the validation is run
	 */
	public function testValidationRun()
	{
		$factory = new Factory($this->validator, array());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testValidationErrorThrowsException()
	{
		$this->validator->shouldReceive('fails')->once()->andReturn(true)
						->shouldReceive('messages')->once()->andReturn(m::mock(array('all' => array())));

		$factory = new Factory($this->validator, array());
	}

	public function testMakeReturnsModel()
	{
		$configMock = m::mock('Frozennode\Administrator\Config\Model\Config');
		$factory = m::mock('Frozennode\Administrator\Config\Factory[parseType,searchMenu,getItemConfigObject]', array($this->validator, array()));
		$factory->shouldReceive('searchMenu')->once()->andReturn(array('test'))
				->shouldReceive('getItemConfigObject')->once()->with(array('test'))->andReturn($configMock);
		$this->assertEquals($factory->make('some_model'), $configMock);
	}

	public function testMakeReturnsFalse()
	{
		$configMock = m::mock('Frozennode\Administrator\Config\Model\Config');
		$factory = m::mock('Frozennode\Administrator\Config\Factory[parseType,searchMenu]', array($this->validator, array()));
		$factory->shouldReceive('searchMenu')->once()->andReturn(false);
		$this->assertEquals($factory->make('some_model'), false);
	}

	public function testUpdateConfigOptions()
	{
		$config = m::mock('Frozennode\Administrator\Config\Config');
		$config->shouldReceive('setOptions')->once();
		$factory = m::mock('Frozennode\Administrator\Config\Factory[searchMenu,getConfig]', array($this->validator, array()));
		$factory->shouldReceive('searchMenu')->once()->andReturn(array())
				->shouldReceive('getConfig')->once()->andReturn($config);
		$factory->updateConfigOptions();
	}

	public function testParseTypeModel()
	{
		$factory = new Factory($this->validator, array());
		$factory->parseType('some_model');
		$this->assertEquals($factory->getType(), 'model');
	}

	public function testParseTypeSettings()
	{
		$factory = new Factory($this->validator, array());
		$factory->parseType('settings.something');
		$this->assertEquals($factory->getType(), 'settings');
	}

	public function testModelInMenu()
	{
		$name = 'some_model';
		$config = array('menu' => array($name));
		$factory = m::mock('Frozennode\Administrator\Config\Factory[fetchConfigFile]', array($this->validator, $config));
		$factory->shouldReceive('fetchConfigFile')->once()->andReturn(array());
		$this->assertEquals($factory->searchMenu($name), array());
	}

	public function testModelInNestedMenu()
	{
		$name = 'some_model';
		$config = array('menu' => array('foo' => array($name)));
		$factory = m::mock('Frozennode\Administrator\Config\Factory[fetchConfigFile]', array($this->validator, $config));
		$factory->shouldReceive('fetchConfigFile')->once()->andReturn(array());
		$this->assertEquals($factory->searchMenu($name), array());
	}

	public function testModelInDeepNestedMenu()
	{
		$name = 'some_model';
		$config = array('menu' => array('foo' => array('bar' => array($name))));
		$factory = m::mock('Frozennode\Administrator\Config\Factory[fetchConfigFile]', array($this->validator, $config));
		$factory->shouldReceive('fetchConfigFile')->once()->andReturn(array());
		$this->assertEquals($factory->searchMenu($name), array());
	}

	public function testModelNotInMenu()
	{
		$name = 'some_model';
		$factory = new Factory($this->validator, array('menu' => array()));
		$this->assertEquals($factory->searchMenu($name), false);
	}

	public function testFetchConfigWorks()
	{
		$name = 'some_model';
		$filename = __DIR__ . '/' . $name . '.php';
		file_put_contents($filename, "<?php return array();");
		$factory = m::mock('Frozennode\Administrator\Config\Factory[getPath,getPrefix]', array($this->validator, array()));
		$factory->shouldReceive('getPath')->once()->andReturn(__DIR__ . '/')
				->shouldReceive('getPrefix')->once()->andReturn('');
		$this->assertEquals($factory->fetchConfigFile($name), array('name' => $name));
		unlink($filename);
	}

	public function testFetchConfigFails()
	{
		$name = 'some_model';
		$factory = m::mock('Frozennode\Administrator\Config\Factory[getPath,getPrefix]', array($this->validator, array()));
		$factory->shouldReceive('getPath')->once()->andReturn(__DIR__ . '/')
				->shouldReceive('getPrefix')->once()->andReturn('');
		$this->assertEquals($factory->fetchConfigFile($name), false);
	}

}