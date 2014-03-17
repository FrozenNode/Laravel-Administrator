<?php namespace Frozennode\Administrator\View;

use Illuminate\Container\Container;
use Illuminate\View\View;

class Composer {

	/**
	 * The ioc container
	 *
	 * @var \Illuminate\Container\Container;
	 */
	protected $container;

	/**
	 * Create a new Composer instance
	 *
	 * @param
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Compose the index view
	 *
	 * @param \Illuminate\View\View		$view
	 *
	 * @return void
	 */
	public function composeIndex(View $view)
	{
		//get a model instance that we'll use for constructing stuff
		$config = App::make('itemconfig');
		$fieldFactory = App::make('admin.field.factory');
		$columnFactory = App::make('admin.column.factory');
		$actionFactory = App::make('admin.action.factory');
		$dataTable = App::make('admin.grid');
		$model = $config->getDataModel();
		$baseUrl = URL::route('admin_dashboard');
		$route = parse_url($baseUrl);

		//add the view fields
		$view->config = $config;
		$view->dataTable = $dataTable;
		$view->primaryKey = $model->getKeyName();
		$view->editFields = $fieldFactory->getEditFields();
		$view->arrayFields = $fieldFactory->getEditFieldsArrays();
		$view->dataModel = $fieldFactory->getDataModel();
		$view->columnModel = $columnFactory->getColumnOptions();
		$view->actions = $actionFactory->getActionsOptions();
		$view->globalActions = $actionFactory->getGlobalActionsOptions();
		$view->actionPermissions = $actionFactory->getActionPermissions();
		$view->filters = $fieldFactory->getFiltersArrays();
		$view->rows = $dataTable->getRows(App::make('db'), $view->filters);
		$view->formWidth = $config->getOption('form_width');
		$view->baseUrl = $baseUrl;
		$view->assetUrl = URL::to('packages/frozennode/administrator/');
		$view->route = $route['path'].'/';
		$view->itemId = isset($view->itemId) ? $view->itemId : null;
	}

	/**
	 * Compose the settings page view
	 *
	 * @param \Illuminate\View\View		$view
	 *
	 * @return void
	 */
	public function composeSettingsPage(View $view)
	{
		$config = App::make('itemconfig');
		$fieldFactory = App::make('admin.field.factory');
		$actionFactory = App::make('admin.action.factory');
		$baseUrl = URL::route('admin_dashboard');
		$route = parse_url($baseUrl);

		//add the view fields
		$view->config = $config;
		$view->editFields = $fieldFactory->getEditFields();
		$view->arrayFields = $fieldFactory->getEditFieldsArrays();
		$view->actions = $actionFactory->getActionsOptions();
		$view->baseUrl = $baseUrl;
		$view->assetUrl = URL::to('packages/frozennode/administrator/');
		$view->route = $route['path'].'/';
	}

	/**
	 * Compose the header
	 *
	 * @param \Illuminate\View\View		$view
	 *
	 * @return void
	 */
	public function composeHeader(View $view)
	{
		$view->menu = App::make('admin.menu')->getMenu();
		$view->settingsPrefix = App::make('admin.config.factory')->getSettingsPrefix();
		$view->pagePrefix = App::make('admin.config.factory')->getPagePrefix();
		$view->configType = App::bound('itemconfig') ? App::make('itemconfig')->getType() : false;
	}

	/**
	 * Compose the layout view
	 *
	 * @param \Illuminate\View\View		$view
	 *
	 * @return void
	 */
	public function composeLayout(View $view)
	{
		//set up the basic asset arrays
		$view->css = [];
		$view->js = [
			'jquery' => asset('packages/frozennode/administrator/js/jquery/jquery-1.8.2.min.js'),
			'jquery-ui' => asset('packages/frozennode/administrator/js/jquery/jquery-ui-1.10.3.custom.min.js'),
			'customscroll' => asset('packages/frozennode/administrator/js/jquery/customscroll/jquery.customscroll.js'),
		];

		//add the non-custom-page css assets
		if (!$view->page && !$view->dashboard)
		{
			$view->css += [
				'jquery-ui' => asset('packages/frozennode/administrator/css/ui/jquery-ui-1.9.1.custom.min.css'),
				'jquery-ui-timepicker' => asset('packages/frozennode/administrator/css/ui/jquery.ui.timepicker.css'),
				'select2' => asset('packages/frozennode/administrator/js/jquery/select2/select2.css'),
				'jquery-colorpicker' => asset('packages/frozennode/administrator/css/jquery.lw-colorpicker.css'),
			];
		}

		//add the package-wide css assets
		$view->css += [
			'customscroll' => asset('packages/frozennode/administrator/js/jquery/customscroll/customscroll.css'),
			'main' => asset('packages/frozennode/administrator/css/main.css'),
		];

		//add the non-custom-page js assets
		if (!$view->page && !$view->dashboard)
		{
			$view->js += [
				'select2' => asset('packages/frozennode/administrator/js/jquery/select2/select2.js'),
				'jquery-ui-timepicker' => asset('packages/frozennode/administrator/js/jquery/jquery-ui-timepicker-addon.js'),
				'ckeditor' => asset('packages/frozennode/administrator/js/ckeditor/ckeditor.js'),
				'ckeditor-jquery' => asset('packages/frozennode/administrator/js/ckeditor/adapters/jquery.js'),
				'markdown' => asset('packages/frozennode/administrator/js/markdown.js'),
				'plupload' => asset('packages/frozennode/administrator/js/plupload/js/plupload.full.js'),
			];

			//localization js assets
			$locale = Config::get('app.locale');

			if ($locale !== 'en')
			{
				$view->js += [
					'plupload-l18n' => asset('packages/frozennode/administrator/js/plupload/js/i18n/'.$locale.'.js'),
					'timepicker-l18n' => asset('packages/frozennode/administrator/js/jquery/localization/jquery-ui-timepicker-'.$locale.'.js'),
					'datepicker-l18n' => asset('packages/frozennode/administrator/js/jquery/i18n/jquery.ui.datepicker-'.$locale.'.js'),
				];
			}

			//remaining js assets
			$view->js += [
				'knockout' => asset('packages/frozennode/administrator/js/knockout/knockout-2.2.0.js'),
				'knockout-mapping' => asset('packages/frozennode/administrator/js/knockout/knockout.mapping.js'),
				'knockout-notification' => asset('packages/frozennode/administrator/js/knockout/KnockoutNotification.knockout.min.js'),
				'knockout-update-data' => asset('packages/frozennode/administrator/js/knockout/knockout.updateData.js'),
				'knockout-custom-bindings' => asset('packages/frozennode/administrator/js/knockout/custom-bindings.js'),
				'accounting' => asset('packages/frozennode/administrator/js/accounting.js'),
				'colorpicker' => asset('packages/frozennode/administrator/js/jquery/jquery.lw-colorpicker.min.js'),
				'history' => asset('packages/frozennode/administrator/js/history/native.history.js'),
				'admin' => asset('packages/frozennode/administrator/js/admin.js'),
				'settings' => asset('packages/frozennode/administrator/js/settings.js'),
			];
		}

		$view->js += ['page' => asset('packages/frozennode/administrator/js/page.js')];
	}
}