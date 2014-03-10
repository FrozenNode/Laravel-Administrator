<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns;

use Mockery as m;

class ActionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The options array
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The Config mock
	 *
	 * @var Mockery
	 */
	protected $config;

	/**
	 * The Column mock
	 *
	 * @var Mockery
	 */
	protected $action;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$this->options = array('action_name' => 'test', 'has_permission' => true);
		$this->action = m::mock('Frozennode\Administrator\Actions\Action', array($this->config, $this->options))
						->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testBuildOptions()
	{
		$this->action->shouldReceive('buildStringOrCallable')->twice();
		$options = $this->action->buildOptions($this->options);
		$this->assertTrue(is_array($options));
		$this->assertTrue($options['messages'] === []);
	}

	public function testBuildStringOrCallableEmpty()
	{
		$this->config->shouldReceive('getDataModel')->once();
		$options = array();
		$this->action->buildStringOrCallable($options, array());
	}

	public function testBuildStringOrCallable()
	{
		$options = array(
			'foo' => 'bar',
			'func' => function ($model)
			{
				return 'not bar';
			}
		);

		$this->config->shouldReceive('getDataModel')->once();
		$this->action->buildStringOrCallable($options, array('foo', 'func'));

		$this->assertEquals($options['foo'], 'bar');
		$this->assertEquals($options['func'], 'not bar');
	}

	public function testPerform()
	{
		$this->action->shouldReceive('getOption')->once()->andReturn(function() {return 'foo';});
		$data = null;
		$this->assertEquals($this->action->perform($data), 'foo');
	}

}