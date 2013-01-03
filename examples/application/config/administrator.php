<?php

return array(

	/**
	 * Page title
	 *
	 * @type string
	 */
	'title' => 'Admin',

	/**
	 * Models
	 *
	 * @type array
	 *
	 * Each item in the array should itself be an array with two required items inside it (title, model) and two optional items (single, permission_check).
	 * The key will be what the user sees as the URI for this model.
	 * This should look something like this:
	 *
	 * 'user' => array(
	 * 		'title' => 'Users', //the title that will be used when displaying the model's page
	 *		'single' => 'user', //The name used for singular items. Film model would be 'film'. BoxOffice model might be 'take'
	 * 		'model' => 'AdminModels\\User', //the string class name of the model you will be using. if you wish to extend your app models directly, you can just pass in 'User'
	 *  	'permission_check' => function() { ... }, //[OPTIONAL] Return bool true if the current user is allowed to access this model. False otherwise
	 * )
	 */
	'models' => array(
		'actor' => array(
			'title' => 'Actors',
			'single' => 'actor',
			'model' => 'AdminModels\\Actor',
		),
		'boxoffice' => array(
			'title' => 'Box Office',
			'single' => 'take',
			'model' => 'AdminModels\\BoxOffice',
		),
		'director' => array(
			'title' => 'Directors',
			'single' => 'director',
			'model' => 'AdminModels\\Director',
		),
		'film' => array(
			'title' => 'Films',
			'single' => 'film',
			'model' => 'AdminModels\\Film',
		),
		'theater' => array(
			'title' => 'Theaters',
			'single' => 'film',
			'model' => 'AdminModels\\Theater',
		),
	),

	/**
	 * Auth Check
	 *
	 * @type closure
	 *
	 * This is a closure that should return true if the current user is allowed to view the admin section
	 */
	'auth_check'=> function()
	{
		return Auth::check();
	},

	/**
	 * Login Path
	 *
	 * @type string
	 *
	 * This is the path where Administrator will send the user if they are not logged in (!Auth::check())
	 */
	'login_path' => 'user/login',

	/**
	 * Redirect key
	 *
	 * @type string
	 *
	 * This comes with the redirection to your login_action. Input::get('redirect') will hold the return URL.
	 */
	'login_redirect_key' => 'redirect',

	/**
	 * Global items per page
	 *
	 * @type NULL|int
	 *
	 * If you set this to an integer value greater than 0, it will override the $per_page static property in ALL of your models
	 */
	'global_per_page' => NULL,

);