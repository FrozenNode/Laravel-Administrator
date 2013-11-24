<?php

use Frozennode\Administrator\ModelHelper;
use Frozennode\Administrator\Fields\Field;

//View Composers

//admin index view
View::composer('administrator::index', function($view)
{
	//get a model instance that we'll use for constructing stuff
	$config = App::make('itemconfig');
	$fieldFactory = App::make('admin_field_factory');
	$columnFactory = App::make('admin_column_factory');
	$actionFactory = App::make('admin_action_factory');
	$dataTable = App::make('admin_datatable');
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
});

//admin settings view
View::composer('administrator::settings', function($view)
{
	$config = App::make('itemconfig');
	$fieldFactory = App::make('admin_field_factory');
	$actionFactory = App::make('admin_action_factory');
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
});

//header view
View::composer(array('administrator::partials.header'), function($view)
{
	$view->menu = App::make('admin_menu')->getMenu();
	$view->settingsPrefix = App::make('admin_config_factory')->getSettingsPrefix();
	$view->pagePrefix = App::make('admin_config_factory')->getPagePrefix();
	$view->configType = App::bound('itemconfig') ? App::make('itemconfig')->getType() : false;
});

//the layout view
View::composer(array('administrator::layouts.default'), function($view)
{
	//set up the basic asset arrays
	$view->css = array();
	$view->js = array(
		'jquery' => asset('packages/frozennode/administrator/js/jquery/jquery-1.8.2.min.js'),
		'jquery-ui' => asset('packages/frozennode/administrator/js/jquery/jquery-ui-1.10.3.custom.min.js'),
		'customscroll' => asset('packages/frozennode/administrator/js/jquery/customscroll/jquery.customscroll.js'),
	);

	//add the non-custom-page css assets
	if (!$view->page && !$view->dashboard)
	{
		$view->css += array(
			'jquery-ui' => asset('packages/frozennode/administrator/css/ui/jquery-ui-1.9.1.custom.min.css'),
			'jquery-ui-timepicker' => asset('packages/frozennode/administrator/css/ui/jquery.ui.timepicker.css'),
			'select2' => asset('packages/frozennode/administrator/js/jquery/select2/select2.css'),
			'jquery-colorpicker' => asset('packages/frozennode/administrator/css/jquery.lw-colorpicker.css'),
		);
	}

	//add the package-wide css assets
	$view->css += array(
		'customscroll' => asset('packages/frozennode/administrator/js/jquery/customscroll/customscroll.css'),
		'main' => asset('packages/frozennode/administrator/css/main.css'),
	);

	//add the non-custom-page js assets
	if (!$view->page && !$view->dashboard)
	{
		$view->js += array(
			'select2' => asset('packages/frozennode/administrator/js/jquery/select2/select2.js'),
			'jquery-ui-timepicker' => asset('packages/frozennode/administrator/js/jquery/jquery-ui-timepicker-addon.js'),
			'ckeditor' => asset('packages/frozennode/administrator/js/ckeditor/ckeditor.js'),
			'ckeditor-jquery' => asset('packages/frozennode/administrator/js/ckeditor/adapters/jquery.js'),
			'markdown' => asset('packages/frozennode/administrator/js/markdown.js'),
			'plupload' => asset('packages/frozennode/administrator/js/plupload/js/plupload.full.js'),
		);

		//localization js assets
		$locale = Config::get('app.locale');

		if ($locale !== 'en')
		{
			$view->js += array(
				'plupload-l18n' => asset('packages/frozennode/administrator/js/plupload/js/i18n/'.$locale.'.js'),
				'timepicker-l18n' => asset('packages/frozennode/administrator/js/jquery/localization/jquery-ui-timepicker-'.$locale.'.js'),
				'datepicker-l18n' => asset('packages/frozennode/administrator/js/jquery/i18n/jquery.ui.datepicker-'.$locale.'.js'),
			);
		}

		//remaining js assets
		$view->js += array(
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
		);
	}

	$view->js += array('page' => asset('packages/frozennode/administrator/js/page.js'));
});