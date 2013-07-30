<?php namespace Frozennode\Administrator;

use Frozennode\Administrator\Config\Factory as ConfigFactory;
use Frozennode\Administrator\Fields\Factory as FieldFactory;
use Frozennode\Administrator\DataTable\Columns\Factory as ColumnFactory;
use Frozennode\Administrator\Actions\Factory as ActionFactory;
use Frozennode\Administrator\DataTable\DataTable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator as LValidator;

class AdministratorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

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

		//make sure the Laravel Validator is using our custom Validator that we can pass to various constructors
		LValidator::resolver(function($translator, $data, $rules, $messages)
		{
			$validator = new \Frozennode\Administrator\Validator($translator, $data, $rules, $messages);
			$validator->setUrlInstance(\App::make('url'));
			return $validator;
		});

		//set up the shared instances
		$this->app['admin_config_factory'] = $this->app->share(function($app)
		{
			$validator = LValidator::make(array(), array());
			return new ConfigFactory($validator, Config::get('administrator::administrator'));
		});

		$this->app['admin_field_factory'] = $this->app->share(function($app)
		{
			$validator = LValidator::make(array(), array());
			return new FieldFactory($validator, $app->make('itemconfig'), $app->make('db'));
		});

		$this->app['admin_datatable'] = $this->app->share(function($app)
		{
			$dataTable = new DataTable($app->make('itemconfig'), $app->make('admin_column_factory'), $app->make('admin_field_factory'));
			$dataTable->setRowsPerPage($app->make('session'), Config::get('administrator::administrator.global_rows_per_page'));

			return $dataTable;
		});

		$this->app['admin_column_factory'] = $this->app->share(function($app)
		{
			$validator = LValidator::make(array(), array());
			return new ColumnFactory($validator, $app->make('itemconfig'), $app->make('db'));
		});

		$this->app['admin_action_factory'] = $this->app->share(function($app)
		{
			$validator = LValidator::make(array(), array());
			return new ActionFactory($validator, $app->make('itemconfig'), $app->make('db'));
		});

		$this->app['admin_menu'] = $this->app->share(function($app)
		{
			return new Menu($app->make('config'), $app->make('admin_config_factory'));
		});

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

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
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
