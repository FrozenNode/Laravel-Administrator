<?php
namespace Frozennode\Administrator\Tests\DataTable\Columns;

use Mockery as m;

class ActionTest extends \PHPUnit_Framework_TestCase {

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
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$options = array('action_name' => 'test', 'has_permission' => true);
		$this->action = m::mock('Frozennode\Administrator\Actions\Action', array($this->validator, $this->config, $options))
						->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testValidates()
	{
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(false);
		$this->action->validateOptions();
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testValidateFails()
	{
		$this->validator->shouldReceive('override')->once()
						->shouldReceive('fails')->once()->andReturn(true)
						->shouldReceive('messages')->once()->andReturn(m::mock(array('all' => array())));
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->action->validateOptions();
	}

	public function testBuild()
	{
		$this->action->shouldReceive('buildStringOrCallable')->twice();
		$this->validator->shouldReceive('arrayGet')->once()->andReturn(array());
		$this->action->build();
	}

	public function testBuildStringOrCallableEmpty()
	{
		$this->config->shouldReceive('getDataModel')->once();
		$this->validator->shouldReceive('arrayGet')->never();
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
		$this->validator->shouldReceive('arrayGet')->twice()->andReturn($options['foo'], $options['func']);
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

	public function testGetOptions()
	{
		$defaults = array(
			'title' => 'Custom Action',
			'has_permission' => true,
			'confirmation' => false,
			'messages' => array(
				'active' => 'Just a moment...',
				'success' => 'Success!',
				'error' => 'There was an error performing this action',
			),
		);
		$defaults['action_name'] = 'test';
		$this->action->shouldReceive('validateOptions')->once()
					->shouldReceive('build')->once();
		$this->assertEquals($this->action->getOptions(), $defaults);
	}

	public function testGetOptionSucceeds()
	{
		$this->action->shouldReceive('getOptions')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->action->getOption('foo'), 'bar');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetOptionFails()
	{
		$this->action->shouldReceive('getOptions')->once()->andReturn(array('action_name' => 'bar'));
		$this->action->getOption('foo');
	}

}