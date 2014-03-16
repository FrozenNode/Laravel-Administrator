<?php
namespace Frozennode\Administrator\Tests\Config;

use Mockery as m;
use Frozennode\Administrator\Config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The Config mock
	 *
	 * @var Mockery
	 */
	protected $config;

	/**
	 * The string class name
	 *
	 * @var string
	 */
	protected $class = 'Frozennode\Administrator\Config\Config';

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->options = ['name' => 'model_name'];
		$this->config = m::mock('Frozennode\Administrator\Config\Config', [$this->options])->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testBuildOptionsWithoutPermissionOption()
	{
		$options = $this->config->buildOptions($this->options);
		$this->assertTrue($options['permission']);
	}

	public function testBuildOptionsWithPermissionOption()
	{
		$this->options['permission'] = function() {return 'foo';};

		$options = $this->config->buildOptions($this->options);
		$this->assertEquals($options['permission'], 'foo');
	}

	public function testValidateDataValidates()
	{
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once();
		$this->assertEquals($this->config->validateData([], [1]), true);
	}

	public function testValidateDataReturnsStringError()
	{
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(true)
						->shouldReceive('messages')->once()->andReturn(m::mock(['all' => []]));;
		$this->assertEquals($this->config->validateData([], [1]), '');
	}
}