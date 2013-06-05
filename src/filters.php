<?php

use Frozennode\Administrator\ModelConfig;
use Frozennode\Administrator\SettingsConfig;

//Filters


//validate_admin filter
Route::filter('validate_admin', function ()
{
	//get the admin check closure that should be supplied in the config
	$permission = Config::get('administrator::administrator.permission');
	$response = $permission();

	//if this is a simple false value, send the user to the login redirect
	if (!$response)
	{
		$loginUrl = URL::to(Config::get('administrator::administrator.login_path', 'user/login'));
		$redirectKey = Config::get('administrator::administrator.login_redirect_key', 'redirect');
		$redirectUri = URL::route('admin_dashboard');

		return Redirect::to($loginUrl)->with($redirectKey, $redirectUri);
	}
	//otherwise if this is a response, return that
	else if (is_a($response, 'Illuminate\Http\JsonResponse') || is_a($response, 'Illuminate\Http\Response'))
	{
		return $response;
	}
	//if it's a redirect, send it back with the redirect uri
	else if (is_a($response, 'Illuminate\\Http\\RedirectResponse'))
	{
		return $response->with($redirectKey, $redirectUri);
	}
});

//validate_model filter
Route::filter('validate_model', function($route, $request)
{
	$modelName = $route->getParameter('model');
	$config = ModelConfig::get($modelName);

	App::singleton('itemconfig', function($app) use ($config)
	{
		return $config;
	});

	//if the model doesn't exist at all, redirect to 404
	if (!$config)
	{
		App::abort(404, 'Page not found');
	}
	//otherwise if this is a response, return that
	else if (is_a($config, 'Illuminate\Http\JsonResponse') || is_a($config, 'Illuminate\Http\Response'))
	{
		return $config;
	}
	//if it's a redirect, send it back with the redirect uri
	else if (is_a($config, 'Illuminate\\Http\\RedirectResponse'))
	{
		return $config->with($redirectKey, $redirectUri);
	}
});

//validate_settings filter
Route::filter('validate_settings', function($route, $request)
{
	$settingsName = $route->getParameter('settings');
	$config = SettingsConfig::get(SettingsConfig::$prefix . $settingsName);

	App::singleton('itemconfig', function($app) use ($config)
	{
		return $config;
	});

	//if the model doesn't exist at all, redirect to 404
	if (!$config)
	{
		App::abort(404, 'Page not found');
	}
});