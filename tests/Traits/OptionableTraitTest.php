<?php
namespace Frozennode\Administrator\Tests\Traits;

use Mockery as m;

class OptionableTraitTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The Validator mock
	 *
	 * @var Mockery
	 */
	protected $validator;

	/**
	 * The OptionableTrait mock
	 *
	 * @var Mockery
	 */
	protected $optionable;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->optionable = $this->createObjectForTrait();
		$this->optionable->setOptionsValidator($this->validator);
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	protected function createObjectForTrait()
	{
		return $this->getObjectForTrait('Frozennode\Administrator\Traits\OptionableTrait');
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