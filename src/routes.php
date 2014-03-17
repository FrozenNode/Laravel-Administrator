<?php

/**
 * Routes
 */
Route::group(array('prefix' => Config::get('administrator::administrator.uri'), 'before' => 'validate_admin'), function()
{
	//Admin Dashboard
	Route::get('/', array(
		'as' => 'admin_dashboard',
		'uses' => 'Frozennode\Administrator\AdminController@dashboard',
	));

	//File Downloads
	Route::get('file_download', array(
		'as' => 'admin_file_download',
		'uses' => 'Frozennode\Administrator\AdminController@fileDownload'
	));

	//Custom Pages
	Route::get('page/{page}', array(
		'as' => 'admin_page',
		'uses' => 'Frozennode\Administrator\AdminController@page'
	));

	Route::group(array('before' => 'validate_settings|post_validate'), function()
	{
		//Settings Pages
		Route::get('settings/{settings}', array(
			'as' => 'admin_settings',
			'uses' => 'Frozennode\Administrator\AdminController@settings'
		));

		//Settings file upload
		Route::post('settings/{settings}/{field}/file_upload', array(
			'as' => 'admin_settings_file_upload',
			'uses' => 'Frozennode\Administrator\AdminController@fileUpload'
		));

		//Display a settings file
		Route::get('settings/{settings}/file', array(
			'as' => 'admin_settings_display_file',
			'uses' => 'Frozennode\Administrator\AdminController@displayFile'
		));

		//CSRF routes
		Route::group(array('before' => 'csrf'), function()
		{
			//Save Item
			Route::post('settings/{settings}/save', array(
				'as' => 'admin_settings_save',
				'uses' => 'Frozennode\Administrator\AdminController@settingsSave'
			));

			//Custom Action
			Route::post('settings/{settings}/custom_action', array(
				'as' => 'admin_settings_custom_action',
				'uses' => 'Frozennode\Administrator\AdminController@settingsCustomAction'
			));
		});
	});

	//Switch locales
	Route::get('switch_locale/{locale}', array(
		'as' => 'admin_switch_locale',
		'uses' => 'Frozennode\Administrator\AdminController@switchLocale'
	));

	//The route group for all other requests needs to validate admin, model, and add assets
	Route::group(array('before' => 'validate_model|post_validate'), function()
	{
		//Model Index
		Route::get('{model}', array(
			'as' => 'admin_index',
			'uses' => 'Frozennode\Administrator\AdminController@index'
		));

		//Get Item
		Route::get('{model}/{id}', array(
			'as' => 'admin_get_item',
			'uses' => 'Frozennode\Administrator\AdminController@item'
		));

		//New Item
		Route::get('{model}/new', array(
			'as' => 'admin_new_item',
			'uses' => 'Frozennode\Administrator\AdminController@item'
		));

		//Update a relationship's items with constraints
		Route::post('{model}/update_options', array(
			'as' => 'admin_update_options',
			'uses' => 'Frozennode\Administrator\AdminController@updateOptions'
		));

		//Display an image or file field's image or file
		Route::get('{model}/file', array(
			'as' => 'admin_display_file',
			'uses' => 'Frozennode\Administrator\AdminController@displayFile'
		));

		//File Uploads
		Route::post('{model}/{field}/file_upload', array(
			'as' => 'admin_file_upload',
			'uses' => 'Frozennode\Administrator\AdminController@fileUpload'
		));

		//Updating Rows Per Page
		Route::post('{model}/rows_per_page', array(
			'as' => 'admin_rows_per_page',
			'uses' => 'Frozennode\Administrator\AdminController@rowsPerPage'
		));

		//CSRF protection in forms
		Route::group(array('before' => 'csrf'), function()
		{
			//Save Item
			Route::post('{model}/{id?}/save', array(
				'as' => 'admin_save_item',
				'uses' => 'Frozennode\Administrator\AdminController@save'
			));

			//Delete Item
			Route::post('{model}/{id}/delete', array(
				'as' => 'admin_delete_item',
				'uses' => 'Frozennode\Administrator\AdminController@delete'
			));

			//Get results
			Route::post('{model}/results', array(
				'as' => 'admin_get_results',
				'uses' => 'Frozennode\Administrator\AdminController@results'
			));

			//Custom Model Action
			Route::post('{model}/custom_action', array(
				'as' => 'admin_custom_model_action',
				'uses' => 'Frozennode\Administrator\AdminController@customModelAction'
			));

			//Custom Item Action
			Route::post('{model}/{id}/custom_action', array(
				'as' => 'admin_custom_model_item_action',
				'uses' => 'Frozennode\Administrator\AdminController@customModelItemAction'
			));
		});
	});
});
