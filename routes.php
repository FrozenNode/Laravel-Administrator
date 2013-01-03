<?php

/**
 * Filters
 */
require __DIR__.'/filters.php';


/**
 * View Composers
 */
require __DIR__.'/viewComposers.php';


/**
 * Routes
 */

Route::get('(:bundle)', array(
	'as' => 'admin_dashboard',
	'uses' => 'administrator::admin@dashboard',
	'before' => 'validate_admin|add_assets', //only needs to validate admin and add assets
));

//The route group for all other requests needs to validate admin, model, and add assets
Route::group(array('before' => 'validate_admin|validate_model|add_assets'), function()
{
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

	//Update a relationship's items with constraints
	Route::post('(:bundle)/(:any)/update_options', array(
		'as' => 'admin_update_options',
		'uses' => 'administrator::admin@update_options'
	));
});

//CSRF protection in forms
Route::group(array('before' => 'validate_admin|validate_model|csrf'), function()
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