<?php
namespace Frozennode\Administrator\Tests;

use Mockery as m;

class EloquentStub extends \Illuminate\Database\Eloquent\Model {}

class ValidatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The UrlGenerator mock
	 *
	 * @var Mockery
	 */
	protected $url;

	/**
	 * The Validator mock
	 *
	 * @var Mockery
	 */
	protected $validator;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->url = m::mock('Illuminate\Routing\UrlGenerator');
		$this->validator = m::mock('Frozennode\Administrator\Validator')->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testOverrideSetsDataRulesAndMessages()
	{
		$this->validator->shouldReceive('setData')->once()
						->shouldReceive('setRules')->once()
						->shouldReceive('setCustomMessages')->once();
		$this->validator->override(array(), array());
	}

	public function testValidateStringSucceeds()
	{
		$this->assertTrue($this->validator->validateString(null, 'string', null));
	}

	public function testValidateStringFails()
	{
		$this->assertFalse($this->validator->validateString(null, null, null));
	}

	public function testValidateDirectorySucceeds()
	{
		$this->assertTrue($this->validator->validateDirectory(null, __DIR__, null));
	}

	public function testValidateDirectoryFails()
	{
		$this->assertFalse($this->validator->validateDirectory(null, null, null));
	}

	public function testValidateArraySucceeds()
	{
		$this->assertTrue($this->validator->validateArray(null, array(), null));
	}

	public function testValidateArrayFails()
	{
		$this->assertFalse($this->validator->validateArray(null, null, null));
	}

	public function testValidateNotEmptySucceeds()
	{
		$this->assertTrue($this->validator->validateNotEmpty(null, array('full'), null));
	}

	public function testValidateNotEmptyFails()
	{
		$this->assertFalse($this->validator->validateNotEmpty(null, array(), null));
	}

	public function testValidateCallableSucceeds()
	{
		$this->assertTrue($this->validator->validateCallable(null, function() {}, null));
	}

	public function testValidateCallableFails()
	{
		$this->assertFalse($this->validator->validateCallable(null, null, null));
	}


	public function testValidateStringOrCallableSucceeds()
	{
		$this->assertTrue($this->validator->validateStringOrCallable(null, function() {}, null));
		$this->assertTrue($this->validator->validateStringOrCallable(null, 'foo', null));
	}

	public function testValidateStringOrCallableFails()
	{
		$this->assertFalse($this->validator->validateStringOrCallable(null, null, null));
	}

	public function testValidateEloquentSucceeds()
	{
		$this->assertTrue($this->validator->validateEloquent(null, 'Frozennode\Administrator\Tests\EloquentStub', null));
	}

	public function testValidateEloquentFails()
	{
		$this->assertFalse($this->validator->validateEloquent(null, null, null));
	}

}