<?php

return array(

	/**
	 * Page title
	 *
	 * @type string
	 */
	'title' => 'Admin',

	/**
	 * The path to your model config directory
	 *
	 * @type string
	 */
	'model_config_path' => path('app') . 'config/administrator',

	/**
	 * The menu structure of the site. Each item should either be the name of the model's config file or an array of names of model config files.
	 * By doing the latter, you can group certain models together. Each name needs to have a config file in your model config path with the same
	 * name. So 'users' would require a 'users.php' file in your model config path.
	 *
	 * @type array
	 *
	 * 	array(
	 *		'Products' => array('products', 'product_images', 'orders'),
	 *		'users',
	 *	)
	 */
	'menu' => array(),

	/**
	 * The permission option is the highest-level authentication check that lets you define a closure that should return true if the current user
	 * is allowed to view the admin section. Any "falsey" response will send the user back to the 'login_path' defined below.
	 *
	 * @type closure
	 */
	'permission'=> function()
	{
		return Auth::check();
	},

	/**
	 * The login path is the path where Administrator will send the user if they fail a permission check
	 *
	 * @type string
	 */
	'login_path' => 'user/login',

	/**
	 * This is the key of the return path that is sent with the redirection to your login_action. Input::get('redirect') will hold the return URL.
	 *
	 * @type string
	 */
	'login_redirect_key' => 'redirect',

	/**
	 * Global default rows per page
	 *
	 * @type NULL|int
	 */
	'global_rows_per_page' => 20,

);