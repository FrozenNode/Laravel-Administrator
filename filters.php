<?php

use Admin\Libraries\ModelHelper;

//Filters

Route::filter('add_assets', function()
{
	$assets = Asset::container('container')->bundle('administrator');

	/**
	 * CSS
	 */

	$assets->add('bootstrap', 'css/bootstrap.css');
	$assets->add('bootstrap-responsive', 'css/bootstrap-responsive.css');
	$assets->add('jquery.ui.css', 'css/ui/jquery-ui-1.9.1.custom.min.css');
	$assets->add('jquery.ui.timepicker.css', 'css/ui/jquery.ui.timepicker.css');
	$assets->add('chosen_css', 'css/chosen.css');
	$assets->add('main_style', 'css/main.css');


	/**
	 * JavaScript
	 */

	//jquery core
	$assets->add('jquery', 'js/jquery/jquery-1.8.2.min.js');

	//jquery chosen and ajax chosen
	$assets->add('jquery-chosen', 'js/jquery/jquery.chosen.min.js');
	$assets->add('jquery-ajax-chosen', 'js/jquery/jquery.ajax-chosen.min.js');

	//jquery template
	$assets->add('jquery-tmpl', 'js/jquery/jquery.tmpl.min.js');

	//jquery ui
	$assets->add('jquery.ui', 'js/jquery/jquery-ui-1.9.1.custom.min.js');

	//jquery timepicker addon
	$assets->add('jquery.ui.timepicker', 'js/jquery/jquery-ui-timepicker-addon.js');

	//ckeditor and jquery adapter
	$assets->add('ckeditor', 'js/ckeditor/ckeditor.js');
	$assets->add('ckeditor-jquery', 'js/ckeditor/adapters/jquery.js');

	//markdown
	$assets->add('markdownjs', 'js/markdown.js');

	//knockout
	$assets->add('knockout', 'js/knockout/knockout-2.2.0.js');

	//knockout plugins
	$assets->add('knockout-mapping', 'js/knockout/knockout.mapping.js');
	$assets->add('knockout-notification', 'js/knockout/KnockoutNotification.knockout.min.js');
	$assets->add('knockout-update-data', 'js/knockout/knockout.updateData.js');

	//knockout custom bindings
	$assets->add('knockout-custom-bindings', 'js/knockout/custom-bindings.js');

	//accountingjs
	$assets->add('accountingjs', 'js/accounting.js');

	//historyjs
	$assets->add('historyjs', 'js/history/native.history.js');

	//and finally the admin js file
	$assets->add('admin', 'js/admin.js');
});


//validate_admin filter
Route::filter('validate_admin', function ()
{
	//get the admin check closure that should be supplied in the config
	$authCheck = Config::get('administrator::administrator.auth_check');

	if (!$authCheck())
	{
		$loginUrl = URL::to(Config::get('administrator::administrator.login_path', 'user/login'));
		$redirectKey = Config::get('administrator::administrator.login_redirect_key', 'redirect');
		$redirectUri = URL::to_route('admin_dashboard');

		return Redirect::to($loginUrl)->with($redirectKey, $redirectUri);
	}
});

//validate_model filter
Route::filter('validate_model', function ()
{
	$modelName = URI::segment(2);
	$model = ModelHelper::getModelInstance($modelName);

	//if the model doesn't exist at all, redirect to 404
	if (!$model)
	{
		return Response::error('404');
	}

	//if the model does exist, check if this user has permission to access it
	if (!ModelHelper::checkPermission($modelName))
	{
		Redirect::to_route('admin_dashboard');
	}
});