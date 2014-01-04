<?php

//Filters

//validate_admin filter
Route::filter('validate_admin', function ()
{
	$configFactory = App::make('admin_config_factory');

	//get the admin check closure that should be supplied in the config
	$permission = Config::get('administrator::administrator.permission');
	$response = $permission();

	//if this is a simple false value, send the user to the login redirect
	if (!$response)
	{
		$loginUrl = URL::to(Config::get('administrator::administrator.login_path', 'user/login'));
		$redirectKey = Config::get('administrator::administrator.login_redirect_key', 'redirect');
		$redirectUri = Request::url();

		return Redirect::guest($loginUrl)->with($redirectKey, $redirectUri);
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
	$modelName = App::make('administrator.4.1') ? $route->parameter('model') : $route->getParameter('model');

	App::singleton('itemconfig', function($app) use ($modelName)
	{
		$configFactory = App::make('admin_config_factory');
		return $configFactory->make($modelName, true);
	});
});

//validate_settings filter
Route::filter('validate_settings', function($route, $request)
{
	$settingsName = App::make('administrator.4.1') ? $route->parameter('settings') : $route->getParameter('settings');

	App::singleton('itemconfig', function($app) use ($settingsName)
	{
		$configFactory = App::make('admin_config_factory');
		return $configFactory->make($configFactory->getSettingsPrefix() . $settingsName, true);
	});
});

Route::filter('post_validate', function($route, $request)
{
	$config = App::make('itemconfig');

	//if the model doesn't exist at all, redirect to 404
	if (!$config)
	{
		App::abort(404, 'Page not found');
	}

	//check the permission
	$p = $config->getOption('permission');

	//if the user is simply not allowed permission to this model, redirect them to the dashboard
	if (!$p)
	{
		return Redirect::to(URL::route('admin_dashboard'));
	}

	//get the settings data if it's a settings page
	if ($config->getType() === 'settings')
	{
		$config->fetchData(App::make('admin_field_factory')->getEditFields());
	}

	//otherwise if this is a response, return that
	if (is_a($p, 'Illuminate\Http\JsonResponse') || is_a($p, 'Illuminate\Http\Response') || is_a($p, 'Illuminate\\Http\\RedirectResponse'))
	{
		return $p;
	}
});
