<?php

$uri = Config::get('administrator::administrator.uri');

/**
 * Routes
 */

Route::when($uri . '/*', 'validate_admin');

Route::get($uri, array(
	'as' => 'admin_dashboard',
	'uses' => 'Frozennode\Administrator\AdminController@dashboard',
	'before' => 'validate_admin', //only needs to validate admin and add assets
));

//The route group for all other requests needs to validate admin, model, and add assets
Route::group(array('before' => 'validate_model'), function() use ($uri)
{
	//Model Index
	Route::get($uri . '/{model}', array(
		'as' => 'admin_index',
		'uses' => 'Frozennode\Administrator\AdminController@index'
	));

	//Get Item
	Route::get($uri . '/{model}/{id}', array(
		'as' => 'admin_get_item',
		'uses' => 'Frozennode\Administrator\AdminController@item'
	))
	->where('id', '[0-9]+');

	//New Item
	Route::get($uri . '/{model}/new', array(
		'as' => 'admin_new_item',
		'uses' => 'Frozennode\Administrator\AdminController@item'
	));

	//Update a relationship's items with constraints
	Route::post($uri . '/{model}/update_options', array(
		'as' => 'admin_update_options',
		'uses' => 'Frozennode\Administrator\AdminController@updateOptions'
	));

	//Display an image or file field's image or file
	Route::get($uri . '/{model}/file', array(
		'as' => 'admin_display_file',
		'uses' => 'Frozennode\Administrator\AdminController@displayFile'
	));
});

//CSRF protection in forms
Route::group(array('before' => 'validate_model|csrf'), function() use ($uri)
{
	//Save Item
	Route::post($uri . '/{model}/{id?}/save', array(
		'as' => 'admin_save_item',
		'uses' => 'Frozennode\Administrator\AdminController@save'
	))
	->where('id', '[0-9]+');

	//Delete Item
	Route::post($uri . '/{model}/{id}/delete', array(
		'as' => 'admin_delete_item',
		'uses' => 'Frozennode\Administrator\AdminController@delete'
	))
	->where('id', '[0-9]+');

	//Get results
	Route::post($uri . '/{model}/results', array(
		'as' => 'admin_get_results',
		'uses' => 'Frozennode\Administrator\AdminController@results'
	));

	//Custom Action
	Route::post($uri . '/{model}/{id}/custom_action', array(
		'as' => 'admin_custom_action',
		'uses' => 'Frozennode\Administrator\AdminController@customAction'
	))
	->where('id', '[0-9]+');
});

//Standard validation without csrf
Route::group(array('before' => 'validate_model'), function() use ($uri)
{
	//File Uploads
	Route::post($uri . '/{model}/{field}/file_upload', array(
		'as' => 'admin_file_upload',
		'uses' => 'Frozennode\Administrator\AdminController@fileUpload'
	));

	//Updating Rows Per Page
	Route::post($uri . '/{model}/rows_per_page', array(
		'as' => 'admin_rows_per_page',
		'uses' => 'Frozennode\Administrator\AdminController@rowsPerPage'
	));
});

//Settings Pages
Route::get($uri . '/settings/{settings}', array(
	'as' => 'admin_settings',
	'before' => 'validate_settings',
	'uses' => 'Frozennode\Administrator\AdminController@settings'
));

//Settings POSTs
Route::group(array('before' => 'validate_settings|csrf'), function() use ($uri)
{
	//Save Item
	Route::post($uri . '/settings/{settings}/save', array(
		'as' => 'admin_settings_save',
		'uses' => 'Frozennode\Administrator\AdminController@settingsSave'
	));

	//Custom Action
	Route::post($uri . '/settings/{settings}/custom_action', array(
		'as' => 'admin_settings_custom_action',
		'uses' => 'Frozennode\Administrator\AdminController@settingsCustomAction'
	));
});

//Settings file upload
Route::post($uri . '/settings/{settings}/{field}/file_upload', array(
	'before' => 'validate_settings',
	'as' => 'admin_settings_file_upload',
	'uses' => 'Frozennode\Administrator\AdminController@fileUpload'
));

//Display a settings file
Route::get($uri . '/settings/{settings}/file', array(
	'before' => 'validate_settings',
	'as' => 'admin_settings_display_file',
	'uses' => 'Frozennode\Administrator\AdminController@displayFile'
));

//Switch locales
Route::get($uri . '/switch_locale/{locale}', array(
	'as' => 'admin_switch_locale',
	'uses' => 'Frozennode\Administrator\AdminController@switchLocale'
));