<?php
use Admin\Libraries\ModelHelper;
use Admin\Libraries\Fields\Field;
use Admin\Libraries\Column;
use Admin\Libraries\Sort;

//The admin group for all REST calls
Route::group(array('before' => 'validate_admin|add_assets'), function()
{
	//Admin Dashboard
	Route::get('(:bundle)', array(
		'as' => 'admin_dashboard',
		'uses' => 'administrator::admin@dashboard'
	));

	//Model Index
	Route::get('(:bundle)/(:any)', array(
		'as' => 'admin_index',
		'uses' => 'administrator::admin@index'
	));

	//Get Item
	Route::get('(:bundle)/(:any)/(:num)', array(
		'as' => 'admin_get_item',
		'uses' => 'administrator::admin@item'
	));

	//New Item
	Route::get('(:bundle)/(:any)/new', array(
		'as' => 'admin_new_item',
		'uses' => 'administrator::admin@item'
	));

	//CSRF protection in forms
	Route::group(array('before' => 'csrf'), function()
	{
		//Save Item
		Route::post('(:bundle)/(:any)/(:num?)/save', array(
			'as' => 'admin_save_item',
			'uses' => 'administrator::admin@save'
		));

		//Delete Item
		Route::post('(:bundle)/(:any)/(:num)/delete', array(
			'as' => 'admin_delete_item',
			'uses' => 'administrator::admin@delete'
		));

		//Get results
		Route::post('(:bundle)/(:any)/results', array(
			'as' => 'admin_get_results',
			'uses' => 'administrator::admin@results'
		));
	});
});


//Filters

Route::filter('add_assets', function()
{
	$assets = Asset::container('container')->bundle('administrator');

	//CSS
	$assets->add('bootstrap', 'css/bootstrap.css');
	$assets->add('bootstrap-responsive', 'css/bootstrap-responsive.css');
	$assets->add('jquery.ui.css', 'css/ui/jquery-ui-1.9.1.custom.min.css');
	$assets->add('jquery.ui.timepicker.css', 'css/ui/jquery.ui.timepicker.css');
	$assets->add('chosen_css', 'css/chosen.css');
	$assets->add('main_style', 'css/main.css');

	//JS
	$assets->add('jquery', 'js/jquery/jquery-1.8.2.min.js');
	$assets->add('jquery-chosen', 'js/jquery/jquery.chosen.min.js');
	$assets->add('jquery-tmpl', 'js/jquery/jquery.tmpl.min.js');

	$assets->add('jquery.ui', 'js/jquery/jquery-ui-1.9.1.custom.min.js');
	$assets->add('jquery.ui.timepicker', 'js/jquery/jquery-ui-timepicker-addon.js');

	$assets->add('knockout', 'js/knockout/knockout-2.2.0.js');
	$assets->add('knockout-mapping', 'js/knockout/knockout.mapping.js');
	$assets->add('knockout-notification', 'js/knockout/KnockoutNotification.knockout.min.js');
	$assets->add('knockout-update-data', 'js/knockout/knockout.updateData.js');
	$assets->add('knockout-custom-bindings', 'js/knockout/custom-bindings.js');

	$assets->add('accountingjs', 'js/accounting.js');

	$assets->add('historyjs', 'js/history/native.history.js');

	$assets->add('admin', 'js/admin.js');
});

Route::filter('validate_admin', function ()
{
	//get the admin check closure that should be supplied in the config
	$auth_check = Config::get('administrator::administrator.auth_check');

	if (!$auth_check())
	{
		$login_url = URL::to(Config::get('administrator::administrator.login_path', 'user/login'));
		$redirect_key = Config::get('administrator::administrator.login_redirect_key', 'redirect');
		$redirect_uri = URL::to_route('admin_dashboard');

		return Redirect::to($login_url)->with($redirect_key, $redirect_uri);
	}
});



//View Composers

//admin index view
View::composer('administrator::index', function($view)
{
	//get a model instance that we'll use for constructing stuff
	$modelInstance = ModelHelper::getModel($view->modelName);


	$columns = Column::getColumns($modelInstance);
	$editFields = Field::getEditFields($modelInstance);
	$bundleConfig = Bundle::get('administrator');

	//add the view fields
	$view->modelTitle = Config::get('administrator::administrator.models.'.$view->modelName.'.title', $view->modelName);
	$view->columns = $columns['columns'];
	$view->includedColumns = $columns['includedColumns'];
	$view->primaryKey = $modelInstance::$key;
	$view->sort = Sort::get($modelInstance)->toArray();
	$view->rows = ModelHelper::getRows($modelInstance, $view->sort);
	$view->editFields = $editFields['arrayFields'];
	$view->dataModel = $editFields['dataModel'];
	$view->filters = ModelHelper::getFilters($modelInstance);
	$view->baseUrl = URL::to_route('admin_index');
	$view->bundleHandles = $bundleConfig['handles'];
	$view->modelInstance = $modelInstance;
	$view->model = isset($view->model) ? $view->model : false;

});