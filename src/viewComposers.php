<?php

use Illuminate\Support\Facades\View;

//admin index view
View::composer('administrator::index', function($view)
{
	//get a model instance that we'll use for constructing stuff
	$config = app('itemconfig');
	$fieldFactory = app('admin_field_factory');
	$columnFactory = app('admin_column_factory');
	$actionFactory = app('admin_action_factory');
	$dataTable = app('admin_datatable');
	$model = $config->getDataModel();
	$baseUrl = route('admin_dashboard');
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
	$view->rows = $dataTable->getRows(app('db'), $view->filters);
	$view->formWidth = $config->getOption('form_width');
	$view->baseUrl = $baseUrl;
	$view->assetUrl = url('packages/frozennode/administrator/');
	$view->route = $route['path'].'/';
	$view->itemId = isset($view->itemId) ? $view->itemId : null;
});

//admin settings view
View::composer('administrator::settings', function($view)
{
	$config = app('itemconfig');
	$fieldFactory = app('admin_field_factory');
	$actionFactory = app('admin_action_factory');
	$baseUrl = route('admin_dashboard');
	$route = parse_url($baseUrl);

	//add the view fields
	$view->config = $config;
	$view->editFields = $fieldFactory->getEditFields();
	$view->arrayFields = $fieldFactory->getEditFieldsArrays();
	$view->actions = $actionFactory->getActionsOptions();
	$view->baseUrl = $baseUrl;
	$view->assetUrl = url('packages/frozennode/administrator/');
	$view->route = $route['path'].'/';
});

//header view
View::composer(array('administrator::partials.header'), function($view)
{
	$view->menu = app('admin_menu')->getMenu();
	$view->settingsPrefix = app('admin_config_factory')->getSettingsPrefix();
	$view->pagePrefix = app('admin_config_factory')->getPagePrefix();
	$view->configType = app()->bound('itemconfig') ? app('itemconfig')->getType() : false;
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
		$locale = config('app.locale');

		if ($locale !== 'en')
		{
			$view->js += array(
				'plupload-l18n' => asset('packages/frozennode/administrator/js/plupload/js/i18n/'.$locale.'.js'),
				'timepicker-l18n' => asset('packages/frozennode/administrator/js/jquery/localization/jquery-ui-timepicker-'.$locale.'.js'),
				'datepicker-l18n' => asset('packages/frozennode/administrator/js/jquery/i18n/jquery.ui.datepicker-'.$locale.'.js'),
				'select2-l18n' => asset('packages/frozennode/administrator/js/jquery/select2/select2_locale_'.$locale.'.js'),
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
