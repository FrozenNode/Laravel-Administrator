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
	 * Each item in the array should itself be an array (with two items inside it: title, model) and it should be indexed on the model name.
	 * This should look something like this:
	 *
	 * 'user' => array(
	 * 		'title' => 'Users', //the title that will be used when displaying the model's page
	 * 		'model' => 'AdminModels\\User', //the string class name of the model you will be using. if you wish to extend your app models directly, you can just pass in 'User'
	 * )
	 */
	'models' => array(),

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