<?php
namespace Frozennode\Administrator\Tests\Fields;

use Mockery as m;

class EloquentStub {
	public $books;
	public $other = 'other_value';
	public $default_value = 'something';
	public function __construct() {
		$this->books = m::mock('Illuminate\Database\Eloquent\Collection');
		$this->books->shouldReceive('toArray')->zeroOrMoreTimes()->andReturn('books_value');
	}
	public function bt() {return m::mock('Illuminate\Database\Eloquent\Relations\BelongsTo');}
	public function btm() {return m::mock('Illuminate\Database\Eloquent\Relations\BelongsToMany');}
	public function hm() {return m::mock('Illuminate\Database\Eloquent\Relations\HasMany');}
	public function ho() {return m::mock('Illuminate\Database\Eloquent\Relations\HasOne');}
	public function bar() {return 'not a relationship';}
}

class FieldStub {}

class FieldFactoryTest extends \PHPUnit_Framework_TestCase {

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
	 * The DB mock
	 *
	 * @var Mockery
	 */
	protected $db;

	/**
	 * The FieldFactory mock
	 *
	 * @var Mockery
	 */
	protected $factory;

	/**
	 * Set up function
	 */
	public function setUp()
	{
		$this->validator = m::mock('Frozennode\Administrator\Validator');
		$this->config = m::mock('Frozennode\Administrator\Config\Model\Config');
		$this->db = m::mock('Illuminate\Database\DatabaseManager');
		$this->factory = m::mock('Frozennode\Administrator\Fields\Factory', array($this->validator, $this->config, $this->db))->makePartial();
	}

	/**
	 * Tear down function
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testMake()
	{
		$this->factory->shouldReceive('prepareOptions')->once()->andReturn('test')
						->shouldReceive('getFieldObject')->once()->with('test')->andReturn('foo');
		$this->assertEquals($this->factory->make('foo', 'bar'), 'foo');
	}

	public function testGetFieldObject()
	{
		$this->factory->shouldReceive('getFieldTypeClass')->once()->andReturn('Frozennode\Administrator\Tests\Fields\FieldStub');
		$this->assertEquals(get_class($this->factory->getFieldObject(array('type' => 'foo'))), get_class(new FieldStub));
	}

	public function testPrepateOptions()
	{
		$name = 'foo';
		$options = array('field_name' => 'foo');
		$this->factory->shouldReceive('validateOptions')->once()->with($name, $options)->andReturn($options)
						->shouldReceive('ensureTypeIsSet')->once()
						->shouldReceive('setRelationshipType')->once()
						->shouldReceive('checkTypeExists')->once();
		$this->assertEquals($this->factory->prepareOptions($name, $options), array_merge($options, array('title' => 'foo')));
	}

	public function testValidateOptionsIntegerNameStringOptions()
	{
		$name = 0;
		$options = 'field';
		$this->assertEquals($this->factory->validateOptions($name, $options), array('field_name' => $options));
	}

	public function testValidateOptionsStringNameArrayOptions()
	{
		$name = 'field';
		$options = array();
		$this->assertEquals($this->factory->validateOptions($name, $options), array('field_name' => $name));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testValidateOptionsIntegerNameArrayOptions()
	{
		$name = 0;
		$options = array();
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->validateOptions($name, $options);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testValidateOptionsStringNameNonStringOptions()
	{
		$name = 'field';
		$options = true;
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->validateOptions($name, $options);
	}

	public function testEnsureTypeIsSetNoType()
	{
		$options = array();
		$this->config->shouldReceive('getType')->once()->andReturn(false);
		$this->factory->ensureTypeIsSet($options);
		$this->assertEquals($options, array('type' => 'text'));
	}

	public function testEnsureTypeIsSetToKey()
	{
		$options = array('field_name' => 'field');
		$this->config->shouldReceive('getType')->once()->andReturn('model')
						->shouldReceive('getDataModel')->andReturn(m::mock(array('getKeyName' => 'field')));
		$this->factory->ensureTypeIsSet($options);
		$this->assertEquals($options, array_merge($options, array('type' => 'key')));
	}

	public function testEnsureTypeIsSetAlreadySet()
	{
		$options = array('type' => 'foo');
		$this->factory->ensureTypeIsSet($options);
		$this->assertEquals($options, array_merge($options, array('type' => 'foo')));
	}

	public function testSetRelationshipTypeSkipsNonRelationships()
	{
		$options = array();
		$this->validator->shouldReceive('arrayGet')->once()->andReturn('foo');
		$this->factory->setRelationshipType($options, false);
		$this->assertEquals($options, array());
	}

	public function testSetRelationshipTypeAddsTypeWithLoadRelationships()
	{
		$options = array('field_name' => 'foo');
		$this->validator->shouldReceive('arrayGet')->twice()->andReturn('relationship', false);
		$this->factory->shouldReceive('getRelationshipKey')->once()->andReturn('foo');
		$this->factory->setRelationshipType($options, true);
		$this->assertEquals($options['type'], 'foo');
		$this->assertEquals($options['load_relationships'], true);
	}

	public function testSetRelationshipTypeWithoutLoadRelationships()
	{
		$options = array('field_name' => 'foo');
		$this->validator->shouldReceive('arrayGet')->once()->andReturn('relationship');
		$this->factory->shouldReceive('getRelationshipKey')->once()->andReturn('foo');
		$this->factory->setRelationshipType($options, false);
		$this->assertEquals($options['load_relationships'], false);
	}

	public function testCheckTypeExistsValidates()
	{
		$options = array('type' => 'text');
		$this->config->shouldReceive('getType')->once()->andReturn('model');
		$this->factory->checkTypeExists($options);
		$this->assertEquals($options, array('type' => 'text'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCheckTypeExistsInvalidType()
	{
		$options = array('type' => 'foo');
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->checkTypeExists($options);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCheckTypeExistsInvalidSettingsType()
	{
		$options = array('type' => 'belongs_to_many');
		$this->config->shouldReceive('getType')->once()->andReturn('settings')
					->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->checkTypeExists($options);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetRelationshipKeyErrorOnMissingMethod()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(new EloquentStub)
					->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->getRelationshipKey('foo');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetRelationshipKeyErrorOnMissingMethodObject()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(new EloquentStub)
					->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->getRelationshipKey('bar');
	}

	public function testGetRelationshipKeyValid()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(new EloquentStub)
					->shouldReceive('getOption')->once()->andReturn('');
		$this->assertEquals($this->factory->getRelationshipKey('btm'), 'belongs_to_many');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testFindFieldMissingField()
	{
		$this->factory->shouldReceive('getEditFields')->once()->andReturn(array());
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->findField('foo');
	}

	public function testFindFieldFound()
	{
		$this->factory->shouldReceive('getEditFields')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->factory->findField('foo'), 'bar');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testFindFilterMissingField()
	{
		$this->factory->shouldReceive('getFilters')->once()->andReturn(array());
		$this->config->shouldReceive('getOption')->once()->andReturn('');
		$this->factory->findFilter('foo');
	}

	public function testFindFilterFound()
	{
		$this->factory->shouldReceive('getFilters')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->factory->findFilter('foo'), 'bar');
	}

	public function testGetEditFieldsReturnsIndexedArray()
	{
		$fieldObject = m::mock(array('getOption' => 'foo'));
		$this->config->shouldReceive('getOption')->once()->andReturn(array('foo' => array()));
		$this->factory->shouldReceive('make')->once()->andReturn($fieldObject);
		$this->assertEquals($this->factory->getEditFields(), array('foo' => $fieldObject));
	}

	public function testGetEditFieldsArraysReturnsIndexedArrayOfOptions()
	{
		$field = m::mock(array('getOption' => 'field', 'getOptions' => 'field_object'));
		$this->config->shouldReceive('getType')->once()->andReturn('model');
		$this->factory->shouldReceive('getEditFields')->once()->andReturn(array($field))
						->shouldReceive('fillKeyField')->once();
		$this->assertEquals($this->factory->getEditFieldsArrays(), array('field' => 'field_object'));
	}

	public function testFillKeyFieldExistingKey()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getKeyName' => 'id')))
						->shouldReceive('getType')->once()->andReturn('model');
		$fields = array('id' => 'field_object');
		$this->factory->fillKeyField($fields);
		$this->assertEquals($fields, array('id' => 'field_object'));
	}

	public function testFillKeyFieldAdded()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getKeyName' => 'id')))
						->shouldReceive('getType')->once()->andReturn('model');
		$this->factory->shouldReceive('make')->once()->andReturn(m::mock(array('getOptions' => 'field_object')));
		$fields = array();
		$this->factory->fillKeyField($fields);
		$this->assertEquals($fields, array('id' => 'field_object'));
	}

	public function testFillKeyFieldSkipsSettings()
	{
		$this->config->shouldReceive('getDataModel')->once()->andReturn(m::mock(array('getKeyName' => 'id')))
						->shouldReceive('getType')->once()->andReturn('settings');
		$fields = array();
		$this->factory->fillKeyField($fields);
		$this->assertEquals($fields, array());
	}

	public function testGetDataModel()
	{
		$fields = array(
			'id' => array('type' => 'key'),
			'books' => array('type' => 'relationship'),
			'other' => array('type' => 'text'),
			'default_value' => array('type' => 'text', 'value' => 'foo'),
		);
		$formatted = array('id' => 0, 'books' => 'books_value', 'other' => null, 'default_value' => 'foo');
		$this->config->shouldReceive('getDataModel')->once()->andReturn(new EloquentStub);
		$this->factory->shouldReceive('getEditFieldsArrays')->once()->andReturn($fields);
		$this->assertEquals($this->factory->getDataModel(), $formatted);
	}

	public function testGetFiltersReturnsIndexedArray()
	{
		$fieldObject = m::mock(array('getOption' => 'foo'));
		$this->config->shouldReceive('getOption')->once()->andReturn(array('foo' => array()));
		$this->factory->shouldReceive('make')->once()->andReturn($fieldObject);
		$this->assertEquals($this->factory->getFilters(), array('foo' => $fieldObject));
	}

	public function testGetFiltersArraysReturnsIndexedArrayOfOptions()
	{
		$field = m::mock(array('getOption' => 'field', 'getOptions' => 'field_object'));
		$this->factory->shouldReceive('getFilters')->once()->andReturn(array('field' => $field));
		$this->assertEquals($this->factory->getFiltersArrays(), array('field' => 'field_object'));
	}

	public function testGetFieldObjectByNameEditFieldFound()
	{
		$this->factory->shouldReceive('getEditFields')->once()->andReturn(array('field' => 'field_object'));
		$this->assertEquals($this->factory->getFieldObjectByName('field', 'edit'), 'field_object');
	}

	public function testGetFieldObjectByNameFilterFound()
	{
		$this->factory->shouldReceive('getFilters')->once()->andReturn(array('field' => 'field_object'));
		$this->assertEquals($this->factory->getFieldObjectByName('field', 'filter'), 'field_object');
	}

	public function testGetFieldObjectByNameFilterNotFound()
	{
		$this->factory->shouldReceive('getFilters')->once()->andReturn(array('field' => 'field_object'));
		$this->assertEquals($this->factory->getFieldObjectByName('other_field', 'filter'), false);
	}

	public function testUpdateRelationshipsEmptyFieldReturnsEmptyArray()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$relatedModel = m::mock(array('getTable' => 'table', 'getKeyName' => 'id'));
		$model = m::mock(array('field' => m::mock(array('getRelated' => $relatedModel))));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->factory->shouldReceive('getFieldObjectByName')->once()->andReturn(false);
		$this->assertEquals($this->factory->updateRelationshipOptions('field', 'filter', array(), array()), array());
	}

	public function testUpdateRelationshipsAutocompleteNoSearchTerm()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$queryCollection = m::mock('Illuminate\Database\Eloquent\Collection');
		$query->shouldReceive('select')->once()
				->shouldReceive('get')->once()->andReturn($queryCollection);
		$relatedModel = m::mock(array('getTable' => 'table', 'getKeyName' => 'id', 'newQuery' => $query));
		$model = m::mock(array('field' => m::mock(array('getRelated' => $relatedModel))));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->db->shouldReceive('raw')->once()
					->shouldReceive('getTablePrefix')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->once()->andReturn(true);
		$this->factory->shouldReceive('getFieldObjectByName')->once()->andReturn($field)
						->shouldReceive('formatSelectedItems')->once()->andReturn(array(1))
						->shouldReceive('filterQueryBySelectedItems')->once()
						->shouldReceive('formatSelectOptions')->once()->andReturn(array('foo'));
		$this->assertEquals($this->factory->updateRelationshipOptions('field', 'filter', array(), array()), array('foo'));
	}

	public function testUpdateRelationshipsAutocompleteNoSearchTermNoSelectedItems()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('select')->once();
		$relatedModel = m::mock(array('getTable' => 'table', 'getKeyName' => 'id', 'newQuery' => $query));
		$model = m::mock(array('field' => m::mock(array('getRelated' => $relatedModel))));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->db->shouldReceive('raw')->once()
					->shouldReceive('getTablePrefix')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->once()->andReturn(true);
		$this->factory->shouldReceive('getFieldObjectByName')->once()->andReturn($field)
						->shouldReceive('formatSelectedItems')->once()->andReturn(array());
		$this->assertEquals($this->factory->updateRelationshipOptions('field', 'filter', array(), array()), array());
	}

	public function testUpdateRelationshipsReturnsOptions()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$queryCollection = m::mock('Illuminate\Database\Eloquent\Collection');
		$query->shouldReceive('select')->once()
				->shouldReceive('get')->once()->andReturn($queryCollection);
		$relatedModel = m::mock(array('getTable' => 'table', 'getKeyName' => 'id', 'newQuery' => $query));
		$model = m::mock(array('field' => m::mock(array('getRelated' => $relatedModel))));
		$this->config->shouldReceive('getDataModel')->once()->andReturn($model);
		$this->db->shouldReceive('raw')->once()
					->shouldReceive('getTablePrefix')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice()->andReturn(false, function() {});
		$this->factory->shouldReceive('getFieldObjectByName')->once()->andReturn($field)
						->shouldReceive('applyConstraints')->once()
						->shouldReceive('filterBySearchTerm')->once()
						->shouldReceive('formatSelectOptions')->once()->andReturn(array('foo' => 'bar'));
		$this->assertEquals($this->factory->updateRelationshipOptions('field', 'filter', array(), array(), 'search'), array('foo' => 'bar'));
	}

	public function testFilterBySearchTermNoTerm()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->never();
		$this->factory->filterBySearchTerm(null, $query, $field, array(), '');
	}

	public function testFilterBySearchTermSelectedItems()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('where')->twice()
				->shouldReceive('take')->once()
				->shouldReceive('whereNotIn')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice()->andReturn(array('first', 'second'), 0);
		$this->db->shouldReceive('raw')->twice()->andReturn('');
		$this->factory->filterBySearchTerm('foo', $query, $field, array(1), '');
	}

	public function testFilterBySearchTermNoSelectedItems()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('where')->twice()
				->shouldReceive('take')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice()->andReturn(array('first', 'second'), 0);
		$this->db->shouldReceive('raw')->twice()->andReturn('');
		$this->factory->filterBySearchTerm('foo', $query, $field, array(), '');
	}

	public function testFormatSelectedItemsArray()
	{
		$this->assertEquals($this->factory->formatSelectedItems(array(1)), array(1));
	}

	public function testFormatSelectedItemsString()
	{
		$this->assertEquals($this->factory->formatSelectedItems('1,2,3'), array(1, 2, 3));
	}

	public function testFormatSelectedItemsEmpty()
	{
		$this->assertEquals($this->factory->formatSelectedItems(false), array());
	}

	public function testFilterQueryBySelectedItems()
	{
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$query->shouldReceive('whereIn')->once()
				->shouldReceive('orderBy')->once();
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->times(3)->andReturn(true);
		$this->factory->filterQueryBySelectedItems($query, array(), $field, '');
	}

	public function testApplyConstraints()
	{
		$relatedModel = m::mock(array('getRelated' => null));
		$otherModel = m::mock(array('getRelated' => null));
		$model = m::mock(array('this_relationship' => $relatedModel, 'key' => $otherModel));
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice()->andReturn(array('key' => 'other_relationship'), 'this_relationship');
		$otherField = m::mock('Frozennode\Administrator\Fields\Field');
		$otherField->shouldReceive('constrainQuery')->once();
		$this->config->shouldReceive('setDataModel')->twice()
						->shouldReceive('getDataModel')->once()->andReturn($model);
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$this->factory->shouldReceive('make')->once()->andReturn($otherField);
		$this->factory->applyConstraints(array('key' => array(1, 2)), $query, $field);
	}

	public function testApplyConstraintsEmpty()
	{
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->once()->andReturn(array());
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$this->factory->applyConstraints(array(), $query, $field);
	}

	public function testApplyConstraintsInvalidConstraintSupplied()
	{
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->once()->andReturn(array('other_key' => 'relationship'));
		$query = m::mock('Illuminate\Database\Eloquent\Builder');
		$this->factory->applyConstraints(array('key' => array(1, 2)), $query, $field);
	}

	public function testFormatSelectOptions()
	{
		$field = m::mock('Frozennode\Administrator\Fields\Field');
		$field->shouldReceive('getOption')->twice()->andReturn('name_field');
		$firstResult = m::mock('stdClass');
		$firstResult->shouldReceive('getKey')->once()->andReturn(1)
					->set('name_field', 'first');
		$secondResult = m::mock('stdClass');
		$secondResult->shouldReceive('getKey')->once()->andReturn(2)
					->set('name_field', 'second');
		$results = new \Illuminate\Database\Eloquent\Collection(array($firstResult, $secondResult));
		$output = array(array('id' => 1, 'text' => 'first'), array('id' => 2, 'text' => 'second'));
		$this->assertEquals($this->factory->formatSelectOptions($field, $results), $output);
	}

}
