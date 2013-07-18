<?php
namespace Frozennode\Administrator\Tests\Config\Settings;

use Mockery as m;

class SettingsConfigTest extends \PHPUnit_Framework_TestCase {

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
		$this->config = m::mock('Frozennode\Administrator\Config\Settings\Config', array($this->validator, array()))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testGetStoragePathAddsAndTrimsTrailingSlash()
	{
		$this->config->shouldReceive('getOption')->twice()->andReturn('/test/path', '/test/path/two/');
		$this->assertEquals($this->config->getStoragePath(), '/test/path/');
		$this->assertEquals($this->config->getStoragePath(), '/test/path/two/');
	}

	public function testFetchData()
	{
		$this->config->shouldReceive('setDataModel')->once()
						->shouldReceive('populateData')->once()->andReturn(array());
		$this->config->fetchData(array());
	}

	public function testPopulateDataNoFile()
	{
		$path = __DIR__ . '/bar/';
		$this->config->shouldReceive('getStoragePath')->once()->andReturn($path)
						->shouldReceive('getOption')->once()->andReturn('foo');
		$this->assertEquals($this->config->populateData(array('foo')), array('foo'));
		$this->assertTrue(is_dir($path));
		rmdir($path);
	}

	public function testPopulateDataExistingFile()
	{
		$path = __DIR__ . '/bar/';
		mkdir($path);
		$output = array('foo' => 2, 'bar' => 3);
		file_put_contents($path . 'foo.json', json_encode($output));
		$this->config->shouldReceive('getStoragePath')->once()->andReturn($path)
						->shouldReceive('getOption')->once()->andReturn('foo');
		$this->assertEquals($this->config->populateData(array('foo' => 'something', 'bar' => 'something')), $output);
		unlink($path . 'foo.json');
		rmdir($path);
	}

	public function testSaveValidates()
	{
		$input = m::mock('Illuminate\Http\Request');
		$input->shouldReceive('get')->twice();
		$this->config->shouldReceive('validateData')->once()
					->shouldReceive('getOption')->once()->andReturn(array())
					->shouldReceive('runBeforeSave')->once()
					->shouldReceive('putToJson')->once()
					->shouldReceive('setDataModel')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice();
		$fields = array('field1' => $field, 'field2' => $field);
		$this->assertTrue($this->config->save($input, $fields));
	}

	public function testSaveValidateFails()
	{
		$input = m::mock('Illuminate\Http\Request');
		$input->shouldReceive('get')->twice();
		$this->config->shouldReceive('validateData')->once()->andReturn('some error')
					->shouldReceive('getOption')->once()->andReturn(array())
					->shouldReceive('runBeforeSave')->never()
					->shouldReceive('putToJson')->never()
					->shouldReceive('setDataModel')->never();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice();
		$fields = array('field1' => $field, 'field2' => $field);
		$this->assertEquals($this->config->save($input, $fields), 'some error');
	}

	public function testSaveBeforeSaveFails()
	{
		$input = m::mock('Illuminate\Http\Request');
		$input->shouldReceive('get')->twice();
		$this->config->shouldReceive('validateData')->once()
					->shouldReceive('getOption')->once()->andReturn(array())
					->shouldReceive('runBeforeSave')->once()->andReturn('some error')
					->shouldReceive('putToJson')->never()
					->shouldReceive('setDataModel')->never();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice();
		$fields = array('field1' => $field, 'field2' => $field);
		$this->assertEquals($this->config->save($input, $fields), 'some error');
	}

	public function testRunBeforeSaveNotCallable()
	{
		$data = array();
		$this->config->shouldReceive('getOption')->once();
		$this->assertTrue($this->config->runBeforeSave($data));
	}

	public function testRunBeforeSaveAltersInput()
	{
		$data = array('foo' => 'bar');
		$this->config->shouldReceive('getOption')->once()->andReturn(function(&$data) {$data['fuzz'] = 'who';});
		$this->assertTrue($this->config->runBeforeSave($data));
		$this->assertEquals($data, array('foo' => 'bar', 'fuzz' => 'who'));
	}

	public function testRunBeforeSaveStringError()
	{
		$data = array('foo' => 'bar');
		$this->config->shouldReceive('getOption')->once()->andReturn(function(&$data) {return 'some error';});
		$this->assertEquals($this->config->runBeforeSave($data), 'some error');
	}

	public function testPutToJson()
	{
		$path = __DIR__ . '/bar/';
		mkdir($path);
		$this->config->shouldReceive('getStoragePath')->once()->andReturn($path)
						->shouldReceive('getOption')->once()->andReturn('foo');
		$this->config->putToJson(array('foo'));
		$json = file_get_contents($path . 'foo.json');
		$data = json_decode($json);
		$this->assertEquals($data, array('foo'));
		unlink($path . 'foo.json');
		rmdir($path);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testPutToJsonNotWritableError()
	{
		$path = __DIR__ . '/bar/';
		$this->config->shouldReceive('getStoragePath')->once()->andReturn($path)
						->shouldReceive('getOption')->once()->andReturn('foo');
		$this->config->putToJson(array('foo'));
	}

}