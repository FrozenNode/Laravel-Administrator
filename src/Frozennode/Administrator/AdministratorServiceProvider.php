<?php namespace Frozennode\Administrator;

use Frozennode\Administrator\Config\Factory as ConfigFactory;
use Frozennode\Administrator\Fields\Factory as FieldFactory;
use Frozennode\Administrator\DataTable\Columns\Factory as ColumnFactory;
use Frozennode\Administrator\Actions\Factory as ActionFactory;
use Frozennode\Administrator\DataTable\DataTable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

use Frozennode\Administrator\Validation\Validator as Validator;
use Illuminate\Support\Facades\Validator as LValidator;

class AdministratorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('frozennode/administrator');

		//set the locale
		$this->setLocale();

		//define a constant that the rest of the package can use to conditionally use pieces of Laravel 4.1.x vs. 4.0.x
		$this->app['administrator.4.1'] = version_compare(\Illuminate\Foundation\Application::VERSION, '4.1') > -1;

		//set up an alias for the base laravel controller to accommodate >=4.1 and <4.1
		if (!class_exists('AdministratorBaseController')){ // Verify alias is not already created
			if ($this->app['administrator.4.1'])
				class_alias('Illuminate\Routing\Controller', 'AdministratorBaseController');
			else
				class_alias('Illuminate\Routing\Controllers\Controller', 'AdministratorBaseController');
		}

		//include our filters, view composers, and routes
		include __DIR__.'/../../filters.php';
		include __DIR__.'/../../viewComposers.php';
		include __DIR__.'/../../routes.php';

		$this->app['events']->fire('administrator.ready');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//the admin validator
		$this->registerValidator();

		//the factories
		$this->registerFactories();

		//the menu
		$this->registerMenu();

		//the data grid
		$this->registerGrid();

		//the resolving callbacks
		$this->registerResolvingCallbacks();

		//load the FieldServiceProvider
		$this->app->register('Frozennode\Administrator\Fields\FieldServiceProvider');
	}

	/**
	 * Registers the Administrator validator
	 */
	protected function registerValidator()
	{
		$this->app['admin.validator'] = $this->app->share(function($app)
		{
			//get the original validator class so we can set it back after creating our own
			$originalValidator = LValidator::make([], []);
			$originalValidatorClass = get_class($originalValidator);

			//temporarily override the core resolver
			LValidator::resolver(function($translator, $data, $rules, $messages) use ($app)
			{
				$validator = new Validator($translator, $data, $rules, $messages);
				$validator->setUrlInstance($app->make('url'));
				return $validator;
			});

			//grab our validator instance
			$validator = LValidator::make([], []);

			//set the validator resolver back to the original validator
			LValidator::resolver(function($translator, $data, $rules, $messages) use ($originalValidatorClass)
			{
				return new $originalValidatorClass($translator, $data, $rules, $messages);
			});

			//return our validator instance
			return $validator;
		});
	}

	/**
	 * Registers the various factories
	 */
	protected function registerFactories()
	{
		//set up the shared instances
		$this->app['admin.config.factory'] = $this->app->share(function($app)
		{
			return new ConfigFactory(Config::get('administrator::administrator'));
		});

		$this->app['admin.field.factory'] = $this->app->share(function($app)
		{
			return new FieldFactory($app->make('admin.validator'), $app->make('itemconfig'), $app->make('db'));
		});

		$this->app['admin.column.factory'] = $this->app->share(function($app)
		{
			return new ColumnFactory($app->make('admin.validator'), $app->make('itemconfig'), $app->make('db'));
		});

		$this->app['admin.action.factory'] = $this->app->share(function($app)
		{
			return new ActionFactory($app->make('admin.validator'), $app->make('itemconfig'), $app->make('db'));
		});
	}

	/**
	 * Registers the Menu
	 */
	protected function registerMenu()
	{
		$this->app['admin.menu'] = $this->app->share(function($app)
		{
			return new Menu($app->make('config'), $app->make('admin.config.factory'));
		});
	}

	/**
	 * Registers the data grid
	 */
	protected function registerGrid()
	{
		$this->app['admin.grid'] = $this->app->share(function($app)
		{
			$dataTable = new DataTable($app->make('itemconfig'), $app->make('admin.column.factory'), $app->make('admin.field.factory'));
			$dataTable->setRowsPerPage($app->make('session.store'), Config::get('administrator::administrator.global.rows.per.page'));

			return $dataTable;
		});
	}

	/**
	 * Registers the IoC resolving callbacks
	 */
	protected function registerResolvingCallbacks()
	{
		//iterate over the provides array and register the callback for each
		foreach ($this->provides() as $abstract)
		{
			$this->app->resolving($abstract, function($object)
			{
				$this->registerTraits($object);
			});
		}
	}

	/**
	 * Injects dependencies into any traits on the supplied object
	 *
	 * @param mixed		$object
	 *
	 * @return void
	 */
	protected function registerTraits($object)
	{
		//iterate over the class's traits
		foreach (class_uses($object) as $trait)
		{
			switch ($trait)
			{
				case 'Frozennode\Administrator\Traits\OptionableTrait':
					$object->setOptionsValidator($this->app->make('admin.validator')); break;
			}
		}
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'admin.validator', 'admin.config.factory', 'admin.field.factory',  'admin.grid',
			'admin.column.factory', 'admin.action.factory', 'admin.menu'
		];
	}

	/**
	 * Sets the locale if it exists in the session and also exists in the locales option
	 *
	 * @return void
	 */
	public function setLocale()
	{
		if ($locale = $this->app->session->get('administrator_locale'))
		{
			$this->app->setLocale($locale);
		}
	}

}
