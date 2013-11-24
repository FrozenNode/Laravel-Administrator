<?php
namespace Frozennode\Administrator\Tests\Config\Model;

use Mockery as m;

class EloquentStub {
	public $exists = true;
	public $field1 = 'foo';
	public static $rules = array('foo');
	public function getTable() {return 'table';}
	public function find() {return $this;}
	public function __unset($name) {unset($this->$name);}
}

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
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config', array($this->validator, array()))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testGetDataModel()
	{
		$stubClass = 'Frozennode\Administrator\Tests\Config\Model\EloquentStub';
		$this->config->shouldReceive('getOption')->once()->andReturn($stubClass);
		$this->assertEquals(get_class($this->config->getDataModel()), $stubClass);
	}

	public function testGetModel()
	{
		$model = new EloquentStub;
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$fields = array('field1' => $field, 'field2' => $field);
		$columns = array('foo' => 'bar', 'field1' => 'bar');
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model)
					->shouldReceive('setExtraModelValues')->once();
		$this->assertEquals($this->config->getModel(5, $fields, $columns), $model);
	}

	public function testSetExtraModelValues()
	{
		$model = new EloquentStub;
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(4)->andReturn(false, true, true, false);
		$fields = array('field1' => $field, 'field2' => $field);
		$columns = array('foo' => 'bar', 'field1' => 'bar');
		$this->config->shouldReceive('setModelRelationship')->once();
		$this->config->setExtraModelValues($fields, $model);
		$this->assertTrue(!isset($model->field1));
	}

	public function testSetModelRelationshipNoRelatedItems()
	{
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(5)->andReturn('field');
		$this->config->shouldReceive('getModelRelatedItems')->once()->andReturn(array());
		$this->config->setModelRelationship($model, $field);
		$this->assertEquals($model->field, array());
	}

	public function testSetModelRelationshipMultipleValuesNoAutocomplete()
	{
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1 = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1->shouldReceive('getKeyName')->once()->andReturn('id');
		$relatedModel1->id = 1;
		$relatedModel1->name = 'model_1';
		$relatedModel2 = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel2->shouldReceive('getKeyName')->once()->andReturn('id');
		$relatedModel2->id = 2;
		$relatedModel2->name = 'model_2';
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(5)->andReturn('field', true, 'name', false, array('test'));
		$relatedItems = array($relatedModel1, $relatedModel2);
		$this->config->shouldReceive('getModelRelatedItems')->once()->andReturn($relatedItems);
		$this->config->setModelRelationship($model, $field);
		$this->assertEquals($model->field, array(1, 2));
		$this->assertEquals($model->field_options, array('test'));
	}

	public function testSetModelRelationshipMultipleValuesAutocomplete()
	{
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1 = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1->shouldReceive('getKeyName')->once()->andReturn('id');
		$relatedModel1->id = 1;
		$relatedModel1->name = 'model_1';
		$relatedModel2 = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel2->shouldReceive('getKeyName')->once()->andReturn('id');
		$relatedModel2->id = 2;
		$relatedModel2->name = 'model_2';
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(5)->andReturn('field', true, 'name', true, array('test'));
		$relatedItems = array($relatedModel1, $relatedModel2);
		$this->config->shouldReceive('getModelRelatedItems')->once()->andReturn($relatedItems);
		$this->config->setModelRelationship($model, $field);
		$this->assertEquals($model->field, array(1, 2));
		$this->assertEquals($model->field_options, array('test'));
		$this->assertEquals($model->field_autocomplete, array(1 => array('id' => 1, 'text' => 'model_1'), 2 => array('id' => 2, 'text' => 'model_2')));
	}

	public function testSetModelRelationshipSingleValueNoAutocomplete()
	{
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1 = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1->shouldReceive('getKeyName')->once()->andReturn('id');
		$relatedModel1->id = 1;
		$relatedModel1->name = 'model_1';
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(5)->andReturn('field', false, 'name', false, array('test'));
		$relatedItems = array($relatedModel1);
		$this->config->shouldReceive('getModelRelatedItems')->once()->andReturn($relatedItems);
		$this->config->setModelRelationship($model, $field);
		$this->assertEquals($model->field, 1);
		$this->assertEquals($model->field_options, array('test'));
	}

	public function testSetModelRelationshipSingleValueAutocomplete()
	{
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1 = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$relatedModel1->shouldReceive('getKeyName')->once()->andReturn('id');
		$relatedModel1->id = 1;
		$relatedModel1->name = 'model_1';
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(5)->andReturn('field', false, 'name', true, array('test'));
		$relatedItems = array($relatedModel1);
		$this->config->shouldReceive('getModelRelatedItems')->once()->andReturn($relatedItems);
		$this->config->setModelRelationship($model, $field);
		$this->assertEquals($model->field, 1);
		$this->assertEquals($model->field_options, array('test'));
		$this->assertEquals($model->field_autocomplete, array(1 => array('id' => 1, 'text' => 'model_1')));
	}

	public function testGetModelRelatedItemsMultipleValuesWithSortField()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('orderBy')->once()->andReturn(m::mock(array('get' => 'foobar')));
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$model->shouldReceive('field')->once()->andReturn($query);
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(3)->andReturn('field', true, 'sort_field');
		$this->assertEquals($this->config->getModelRelatedItems($model, $field), 'foobar');
	}

	public function testGetModelRelatedItemsMultipleValuesWithoutSortField()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('get')->once()->andReturn('foobar');
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$model->shouldReceive('field')->once()->andReturn($query);
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(3)->andReturn('field', true, false);
		$this->assertEquals($this->config->getModelRelatedItems($model, $field), 'foobar');
	}

	public function testGetModelRelatedItemsSingleValue()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('get')->once()->andReturn('foobar');
		$model = m::mock('Illuminate\Database\Eloquent\Model')->makePartial();
		$model->shouldReceive('field')->once()->andReturn($query);
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(2)->andReturn('field', false);
		$this->assertEquals($this->config->getModelRelatedItems($model, $field), 'foobar');
	}

	public function testUpdateModelWithLink()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->shouldReceive('find')->once()
				->shouldReceive('getKey')->once()
				->shouldReceive('setAttribute')->times(4);
		$fieldFactory = m::mock('Frozennode\Administrator\Fields\Factory');
		$fieldFactory->shouldReceive('getEditFieldsArrays')->once();
		$actionFactory = m::mock('Frozennode\Administrator\Actions\Factory');
		$actionFactory->shouldReceive('getActionsOptions')->once()
						->shouldReceive('getActionPermissions')->once();
		$this->config->shouldReceive('setDataModel')->once()
						->shouldReceive('getModelLink')->once()->andReturn(true);
		$this->assertEquals($this->config->updateModel($model, $fieldFactory, $actionFactory), $model);
	}

	public function testUpdateModelWithoutLink()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->shouldReceive('find')->once()
				->shouldReceive('getKey')->once()
				->shouldReceive('setAttribute')->times(3);
		$fieldFactory = m::mock('Frozennode\Administrator\Fields\Factory');
		$fieldFactory->shouldReceive('getEditFieldsArrays')->once();
		$actionFactory = m::mock('Frozennode\Administrator\Actions\Factory');
		$actionFactory->shouldReceive('getActionsOptions')->once()
						->shouldReceive('getActionPermissions')->once();
		$this->config->shouldReceive('setDataModel')->once()
						->shouldReceive('getModelLink')->once();
		$this->assertEquals($this->config->updateModel($model, $fieldFactory, $actionFactory), $model);
	}

	public function testSaveNoUpdatePermission()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->exists = false;
		$model->shouldReceive('find')->once();
		$input = m::mock('Illuminate\Http\Request');
		$fields = array();
		$actionPermissions = array('update' => false);
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
		$this->assertTrue(is_string($this->config->save($input, $fields, $actionPermissions)));
	}

	public function testSaveNoCreatePermission()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->exists = false;
		$model->shouldReceive('find')->once();
		$input = m::mock('Illuminate\Http\Request');
		$fields = array();
		$actionPermissions = array('update' => true, 'create' => false);
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model);
		$this->assertTrue(is_string($this->config->save($input, $fields, $actionPermissions)));
	}

	public function testSaveValidates()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->exists = false;
		$model->shouldReceive('find')->once()
				->shouldReceive('save')->once();
		$input = m::mock('Illuminate\Http\Request');
		$fields = array();
		$actionPermissions = array('update' => true, 'create' => true);
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model)
						->shouldReceive('fillModel')->once()
						->shouldReceive('prepareDataAndRules')->once()->andReturn(array('data' => array(), 'rules' => array()))
						->shouldReceive('validateData')->once()
						->shouldReceive('saveRelationships')->once()
						->shouldReceive('setDataModel')->once();
		$this->assertTrue($this->config->save($input, $fields, $actionPermissions));
	}

	public function testSaveValidateFails()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->exists = false;
		$model->shouldReceive('find')->once()
				->shouldReceive('save')->never();
		$input = m::mock('Illuminate\Http\Request');
		$fields = array();
		$actionPermissions = array('update' => true, 'create' => true);
		$this->config->shouldReceive('getDataModel')->twice()->andReturn($model)
						->shouldReceive('fillModel')->once()
						->shouldReceive('prepareDataAndRules')->once()->andReturn(array('data' => array(), 'rules' => array()))
						->shouldReceive('validateData')->once()->andReturn('some error')
						->shouldReceive('saveRelationships')->never()
						->shouldReceive('setDataModel')->never();
		$this->assertEquals($this->config->save($input, $fields, $actionPermissions), 'some error');
	}

	public function testPrepareDataAndRulesExistingModel()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->exists = true;
		$model->shouldReceive('getDirty')->once()->andReturn(array('key' => 'something', 'secondkey' => 'somethingelse'));
		$this->config->shouldReceive('getModelValidationRules')->once()->andReturn(array('key' => 'somerule', 'other' => 'somerule'));
		extract($this->config->prepareDataAndRules($model));
		$this->assertEquals($rules, array('key' => 'somerule'));
		$this->assertEquals($data, array('key' => 'something', 'secondkey' => 'somethingelse'));
	}

	public function testPrepareDataAndRulesNewModel()
	{
		$model = m::mock('stdClass')->makePartial();
		$model->exists = false;
		$model->shouldReceive('getAttributes')->once()->andReturn(array('key' => 'something', 'secondkey' => 'somethingelse'));
		$this->config->shouldReceive('getModelValidationRules')->once()->andReturn(array('key' => 'somerule', 'other' => 'somerule'));
		extract($this->config->prepareDataAndRules($model));
		$this->assertEquals($rules, array('key' => 'somerule', 'other' => 'somerule'));
		$this->assertEquals($data, array('key' => 'something', 'secondkey' => 'somethingelse'));
	}

	public function testFillModel()
	{
		$input = m::mock('Illuminate\Http\Request');
		$input->shouldReceive('get')->times(3);
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(3)->andReturn(false, 'text', false)
				->shouldReceive('fillModel')->once();
		$field_external = m::mock('Frozennode\Administrator\Fields\Field');
		$field_external->shouldReceive('getOption')->times(3)->andReturn(true, 'belongs_to_many', false);
		$field_setter = m::mock('Frozennode\Administrator\Fields\Field');
		$field_setter->shouldReceive('getOption')->times(3)->andReturn(false, 'text', true)
					->shouldReceive('fillModel')->once();
		$field_password = m::mock('Frozennode\Administrator\Fields\Field');
		$field_password->shouldReceive('getOption')->times(3)->andReturn(false, 'password', false)
					->shouldReceive('fillModel')->once();
		$model = m::mock('stdClass')->makePartial();
		$model->field = 'field_value';
		$model->field_external = 'field_external_value';
		$model->field_setter = 'field_setter_value';
		$model->field_password = '';
		$model->shouldReceive('__unset')->times(3);
		$fields = array('field_external' => $field_external, 'field_setter' => $field_setter, 'field_password' => $field_password, 'field' => $field);
		$this->config->fillModel($model, $input, $fields);
	}

	public function testGetModelValidationRulesNoRules()
	{
		$this->config->shouldReceive('getOption')->once()
						->shouldReceive('getModelStaticValidationRules')->once();
		$this->assertEquals($this->config->getModelValidationRules(), array());
	}

	public function testGetModelValidationRulesOptionsRules()
	{
		$this->config->shouldReceive('getOption')->once()->andReturn(array('foo'))
						->shouldReceive('getModelStaticValidationRules')->never();
		$this->assertEquals($this->config->getModelValidationRules(), array('foo'));
	}

	public function testGetModelValidationRulesModelRules()
	{
		$this->config->shouldReceive('getOption')->once()
						->shouldReceive('getModelStaticValidationRules')->once()->andReturn(array('foo'));
		$this->assertEquals($this->config->getModelValidationRules(), array('foo'));
	}

	public function testGetModelStaticValidationRules()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(new EloquentStub);
		$this->assertEquals($this->config->getModelStaticValidationRules(), array('foo'));
	}

	public function testGetModelStaticValidationRulesNoRules()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(new \stdClass);
		$this->assertEquals($this->config->getModelStaticValidationRules(), false);
	}

	public function testSaveRelationships()
	{
		$model = m::mock('Illuminate\Database\Eloquent\Model');
		$input = m::mock('Illuminate\Http\Request');
		$input->shouldReceive('get')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(3)->andReturn(false, true, false)
				->shouldReceive('fillModel')->once();
		$fields = array('field1' => $field, 'field2' => $field, 'field3' => $field);
		$this->config->saveRelationships($input, $model, $fields);
	}

	public function testGetModelLink()
	{
		$this->config->shouldReceive('getOption')->once()->andReturn(function($model) {return $model;})
						->shouldReceive('getDataModel')->andReturn('foo');
		$this->assertEquals($this->config->getModelLink(), 'foo');
	}

	public function testGetModelLinkNotCallable()
	{
		$this->config->shouldReceive('getOption')->once();
		$this->assertEquals($this->config->getModelLink(), false);
	}

	public function testRunQueryFilterNoFilter()
	{
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->never();
		$this->config->shouldReceive('getOption')->once();
		$this->config->runQueryFilter($query);
	}

	public function testRunQueryFilterWithFilter()
	{
		$filter = function($query) {
			$query->where('test', '=', 'herp');
		};
		$query = m::mock('Illuminate\Database\Query\Builder');
		$query->shouldReceive('where')->once();
		$this->config->shouldReceive('getOption')->once()->andReturn($filter);
		$this->config->runQueryFilter($query);
	}

}