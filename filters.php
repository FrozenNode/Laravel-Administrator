<?php

use Admin\Libraries\ModelConfig;
use Admin\Libraries\SettingsConfig;

//Filters

Route::filter('add_assets', function()
{
	$assets = Asset::container('container')->bundle('administrator');

	/**
	 * CSS
	 */

	$assets->add('jquery.ui.css', 'css/ui/jquery-ui-1.9.1.custom.min.css');
	$assets->add('jquery.ui.timepicker.css', 'css/ui/jquery.ui.timepicker.css');
	$assets->add('select2_css', 'js/jquery/select2/select2.css');
	$assets->add('jquery.lw-colorpicker', 'css/jquery.lw-colorpicker.css');
	$assets->add('main_style', 'css/main.css');


	/**
	 * JavaScript
	 */

	//jquery core
	$assets->add('jquery', 'js/jquery/jquery-1.8.2.min.js');

	//select2
	$assets->add('jquery-select2', 'js/jquery/select2/select2.js');

	//jquery ui
	$assets->add('jquery.ui', 'js/jquery/jquery-ui-1.10.2.custom.min.js');

	//jquery timepicker addon
	$assets->add('jquery.ui.timepicker', 'js/jquery/jquery-ui-timepicker-addon.js');

	//ckeditor and jquery adapter
	$assets->add('ckeditor', 'js/ckeditor/ckeditor.js');
	$assets->add('ckeditor-jquery', 'js/ckeditor/adapters/jquery.js');

	//markdown
	$assets->add('markdownjs', 'js/markdown.js');

	//plupload
	$assets->add('plupload-js', 'js/plupload/js/plupload.full.js');

	if (Config::get('application.language') != "en")
	{
		$assets->add('plupload-lang-js', 'js/plupload/js/i18n/'.Config::get('application.language').'.js');
		$assets->add('jquery.ui.timepicker-lang', 'js/jquery/localization/jquery-ui-timepicker-'.Config::get('application.language').'.js');
		$assets->add('jquery.ui-lang', 'js/jquery/i18n/jquery.ui.datepicker-'.Config::get('application.language').'.js');
	}


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

	//color picker
	$assets->add('jquery.lw-colorpicker', 'js/jquery/jquery.lw-colorpicker.min.js');

	//historyjs
	$assets->add('historyjs', 'js/history/native.history.js');

	//and finally the site scripts
	$assets->add('page', 'js/page.js');
	$assets->add('admin', 'js/admin.js');
	$assets->add('settings', 'js/settings.js');
});


//validate_admin filter
Route::filter('validate_admin', function ()
{
	//set the config items if a user has provided an application config
	foreach (Config::get('administrator::administrator', array()) as $key => $option)
	{
		if (Config::has('administrator.'.$key))
		{
			Config::set('administrator::administrator.'.$key, Config::get('administrator.'.$key));
		}
	}

	//get the admin check closure that should be supplied in the config
	$permission = Config::get('administrator::administrator.permission');

	if (!$permission())
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
	$modelName = Request::route()->parameters[0];
	$config = ModelConfig::get($modelName);

	//if the model doesn't exist at all, redirect to 404
	if (!$config)
	{
		return Response::error('404');
	}

	Request::route()->parameters[0] = $config;
});

//validate_settings filter
Route::filter('validate_settings', function ()
{
	$settingsName = Request::route()->parameters[0];
	$config = SettingsConfig::get(SettingsConfig::$prefix . $settingsName);

	//if the model doesn't exist at all, redirect to 404
	if (!$config)
	{
		return Response::error('404');
	}

	Request::route()->parameters[0] = $config;
});