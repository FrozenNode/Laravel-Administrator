<?php namespace Frozennode\Administrator;

use Frozennode\Administrator\Config\Factory as ConfigFactory;
use Frozennode\Administrator\Fields\Factory as FieldFactory;
use Frozennode\Administrator\DataTable\Columns\Factory as ColumnFactory;
use Frozennode\Administrator\Actions\Factory as ActionFactory;
use Frozennode\Administrator\DataTable\DataTable;
use Frozennode\Administrator\Routing\Filter;
use Frozennode\Administrator\View\Composer;
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
		$path = realpath(dirname(__FILE__) . '/../');
		$this->package('frozennode/administrator', null, $path);

		//include the routes
		include __DIR__.'/Routing/routes.php';

		//set the locale
		$this->setLocale();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//the admin manager
		$this->registerManager();

		//the admin validator
		$this->registerValidator();

		//registers the route filters
		$this->registerRouteFilters();

		//registers the route filters
		$this->registerViewComposers();

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
	 * Registers the Manager
	 */
	protected function registerManager()
	{
		$this->app['admin.manager'] = $this->app->share(function($app)
		{
			return new Manager;
		});
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
				return new Validator($translator, $data, $rules, $messages);
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
	 * Registers the admin filters
	 */
	protected function registerRouteFilters()
	{
		$this->app['admin.routing.filter'] = $this->app->share(function($app)
		{
			return new Filter($app);
		});

		//base admin filter
		$this->app['router']->filter('admin.base', 'admin.routing.filter@filterAdmin');

		//eloquent page filter
		$this->app['router']->filter('admin.page.eloquent', 'admin.routing.filter@filterEloquentPage');

		//settings page filter
		$this->app['router']->filter('admin.page.settings', 'admin.routing.filter@filterSettingsPage');

		//the base admin filter
		$this->app['router']->filter('validate_admin', 'admin.routing.filter@filterAdmin');
	}

	/**
	 * Registers the view composers
	 */
	protected function registerViewComposers()
	{
		$this->app['admin.view.composer'] = $this->app->share(function($app)
		{
			return new Composer($app);
		});

		//index
		$this->app['view']->composer('administrator::index', 'admin.view.composer@composeIndex');

		//index
		$this->app['view']->composer('administrator::settings', 'admin.view.composer@composeSettingsPage');

		//index
		$this->app['view']->composer('administrator::partials.header', 'admin.view.composer@composeHeader');

		//index
		$this->app['view']->composer('administrator::layouts.default', 'admin.view.composer@composeLayout');

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
			'admin.manager', 'admin.validator', 'admin.routing.filter', 'admin.view.composer', 'admin.config.factory',
			'admin.field.factory', 'admin.grid', 'admin.column.factory', 'admin.action.factory', 'admin.menu'
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