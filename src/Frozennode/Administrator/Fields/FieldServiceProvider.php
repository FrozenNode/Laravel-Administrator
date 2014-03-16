<?php namespace Frozennode\Administrator\Fields;

use Illuminate\Support\ServiceProvider;

class FieldServiceProvider extends ServiceProvider {

	/**
	 * The admin manager instance
	 *
	 * @var \Frozennode\Administrator\Manager
	 */
	protected $admin;

	/**
	 * The "simple" fields that have no class dependencies
	 *
	 * @var array
	 */
	protected $simpleFields = [
		'bool' => 'Bool',
		'color' => 'Color',
		'date' => 'Date',
		'datetime' => 'DateTime',
		'enum' => 'Enum',
		'file' => 'File',
		'image' => 'Image',
		'key' => 'Key',
		'markdown' => 'Markdown',
		'number' => 'Number',
		'password' => 'Password',
		'text' => 'Text',
		'textarea' => 'Textarea',
		'time' => 'Time',
		'wysiwyg' => 'Wysiwyg',
	];

	/**
	 * The relationship fields
	 *
	 * @var array
	 */
	protected $relationshipFields = [
		'belongs_to' => 'BelongsTo',
		'belongs_to_many' => 'BelongsToMany',
		'has_many' => 'HasMany',
		'has_one' => 'HasOne',
	];

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//set the admin manager
		$this->admin = $this->app->make('admin.manager');

		//register the fields
		$this->registerSimpleFields();

		//register the fields
		$this->registerComplexFields();

		//register the fields
		$this->registerRelationshipFields();
	}

	/**
	 * Registers the Bool field
	 */
	protected function registerSimpleFields()
	{
		//bind each of the fields
		foreach ($this->simpleFields as $name => $class)
		{
			$class = '\Frozennode\Administrator\Fields\Types\\' . $class;

			$this->app['admin.fields.types.' . $name] = $this->app->bind(function($app, $options) use ($class)
			{
				return new $class($options);
			});

			$this->admin->registerField($name, 'admin.fields.types.' . $name);
		}
	}

	/**
	 * Registers the Bool field
	 */
	protected function registerComplexFields()
	{

	}

	/**
	 * Registers the Bool field
	 */
	protected function registerRelationshipFields()
	{
		//bind each of the fields
		foreach ($this->relationshipFields as $name => $class)
		{
			$class = '\Frozennode\Administrator\Fields\Types\Relationships\\' . $class;

			$this->app['admin.fields.types.relationships.' . $name] = $this->app->bind(function($app, $options) use ($class)
			{
				return new $class($options);
			});

			$this->admin->registerField($name, 'admin.fields.types.relationships.' . $name);
		}
	}
}