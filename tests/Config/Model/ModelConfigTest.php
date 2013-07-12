<?php
namespace Frozennode\Administrator\Tests\Config\Model;

use Mockery as m;
use Frozennode\Administrator\Config\Model\Config as ModelConfig;

class ModelConfigTest extends \PHPUnit_Framework_TestCase {

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
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(false);
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config')->makePartial();
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
		$config = new ModelConfig($this->validator, array());
	}
/*
	public function testMakeReturnsModel()
	{
		$configMock = m::mock('Frozennode\Administrator\Config\Model\Config');
		$factory = m::mock('Frozennode\Administrator\Config\Factory[parseType,searchMenu,getItemConfigObject]', [$this->validator, []]);
		$factory->shouldReceive('parseType')->once()
				->shouldReceive('searchMenu')->once()->andReturn(['test'])
				->shouldReceive('getItemConfigObject')->once()->with(['test'])->andReturn($configMock);
		$this->assertEquals($factory->make('some_model'), $configMock);
	}

	public function testMakeReturnsFalse()
	{
		$configMock = m::mock('Frozennode\Administrator\Config\Model\Config');
		$factory = m::mock('Frozennode\Administrator\Config\Factory[parseType,searchMenu]', [$this->validator, []]);
		$factory->shouldReceive('parseType')->once()
				->shouldReceive('searchMenu')->once()->andReturn(false);
		$this->assertEquals($factory->make('some_model'), false);
	}

	public function testParseTypeModel()
	{
		$factory = new Factory($this->validator, []);
		$factory->parseType('some_model');
		$this->assertEquals($factory->getType(), 'model');
	}

	public function testParseTypeSettings()
	{
		$factory = new Factory($this->validator, []);
		$factory->parseType('settings.something');
		$this->assertEquals($factory->getType(), 'settings');
	}

	public function testModelInMenu()
	{
		$name = 'some_model';
		$config = ['menu' => [$name]];
		$factory = m::mock('Frozennode\Administrator\Config\Factory[fetchConfigFile]', [$this->validator, $config]);
		$factory->shouldReceive('fetchConfigFile')->once()->andReturn([]);
		$this->assertEquals($factory->searchMenu($name), []);
	}

	public function testModelInNestedMenu()
	{
		$name = 'some_model';
		$config = ['menu' => ['foo' => [$name]]];
		$factory = m::mock('Frozennode\Administrator\Config\Factory[fetchConfigFile]', [$this->validator, $config]);
		$factory->shouldReceive('fetchConfigFile')->once()->andReturn([]);
		$this->assertEquals($factory->searchMenu($name), []);
	}

	public function testModelInDeepNestedMenu()
	{
		$name = 'some_model';
		$config = ['menu' => ['foo' => ['bar' => [$name]]]];
		$factory = m::mock('Frozennode\Administrator\Config\Factory[fetchConfigFile]', [$this->validator, $config]);
		$factory->shouldReceive('fetchConfigFile')->once()->andReturn([]);
		$this->assertEquals($factory->searchMenu($name), []);
	}

	public function testModelNotInMenu()
	{
		$name = 'some_model';
		$factory = new Factory($this->validator, ['menu' => []]);
		$this->assertEquals($factory->searchMenu($name), false);
	}

	public function testFetchConfigWorks()
	{
		$name = 'some_model';
		$filename = __DIR__ . '/' . $name . '.php';
		file_put_contents($filename, "<?php return array();");
		$factory = m::mock('Frozennode\Administrator\Config\Factory[getPath,getPrefix]', [$this->validator, []]);
		$factory->shouldReceive('getPath')->once()->andReturn(__DIR__ . '/')
				->shouldReceive('getPrefix')->once()->andReturn('');
		$this->assertEquals($factory->fetchConfigFile($name), ['name' => $name]);
		unlink($filename);
	}

	public function testFetchConfigFails()
	{
		$name = 'some_model';
		$factory = m::mock('Frozennode\Administrator\Config\Factory[getPath,getPrefix]', [$this->validator, []]);
		$factory->shouldReceive('getPath')->once()->andReturn(__DIR__ . '/')
				->shouldReceive('getPrefix')->once()->andReturn('');
		$this->assertEquals($factory->fetchConfigFile($name), false);
	}
*/
}