# Configuration

- [Introduction](#introduction)
- [Options](#options)

<a name="introduction"></a>
## Introduction

Once the bundle is installed, create a new file in your application config called administrator.php (`application/config/administrator.php`). Then copy the contents of the bundle's config file (`bundles/administrator/config/administrator.php`) and paste it into the application config file you just created. This will be where you define the bundle-wide configuration options.

All of the configuration options are used, but not all of them must be supplied. When Administrator loads up, it overwrites the default options set in `bundles/administrator/config/administrator.php` with the options you set in `application/config/administrator.php`.

<a name="options"></a>
## Options

Below is a list of all the available options:

- [Title](#title)
- [Model Config Path](#model-config-path)
- [Menu](#menu)
- [Permission](#permission)
- [Login Path](#login-path)
- [Redirect Key](#redirect-key)
- [Global Rows Per Page](#global-rows-per-page)

<a name="title"></a>
### Title

	/**
	 * Page title
	 *
	 * @type string
	 */
	'title' => 'Admin',

This is the title of the administrative interface displayed to the user at the top left of the page.

<a name="model-config-path"></a>
### Model Config Path

	/**
	 * The path to your model config directory
	 *
	 * @type string
	 */
	'model_config_path' => path('app') . 'config/administrator',

This is the directory location of your application's model config files. It's recommended to use a subdirectory of your application config called `administrator`, but you can put it anywhere you like.

<a name="menu"></a>
### Menu

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
	'menu' => array(
		'E-Commerce' => array('collections', 'products', 'product_images', 'orders'),
		'homepage_sliders',
		'users',
		'roles',
		'colors',
	),

The menu option is where you set the menu structure of the site. If you don't want any submenus, simply provide the name of your model config. The value has to be exactly equal (if you're using Linux, that means case-sensitive) to the name of the model config php file.

So in the above example, there would need to be (in the directory you specified in the `model_config_path`), config files called `collections.php`, `products.php`, `product_images.php`, `orders.php`, `homepage_sliders.php`, `users.php`, `roles.php`, and `colors.php`.

If you want to have a submenu, instead of passing in a string, pass in an array of strings. The index of this slot will be the submenu's title in the UI.

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/menu.png" />

> For a detailed description of all the model configuration options, see the **[model configuration docs](/docs/model-configuration)**

<a name="permission"></a>
### Permission

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

The permission option lets you define a closure that determines whether or not the current user can access all of Administrator. You can also define per-model permissions in each model's config. A user will only be given access if this resolves to a truthy value. If this fails, the user will be redirected to the `login_path`.

<a name="login-path"></a>
### Login Path

	/**
	 * This is the path where Administrator will send the user if they fail the permission check
	 *
	 * @type string
	 */
	'login_path' => 'user/login',

Provide any value that would work with Laravel's `URL::to()` method.

<a name="redirect-key"></a>
### Redirect Key

	/**
	 * The input key of the return url sent to the login_path. You can get this by doing Input::get('redirect')
	 *
	 * @type string
	 */
	'login_redirect_key' => 'redirect',

When a user is redirected to the `login_path`, the redirect path is sent with them. This option lets you define the key. Using the above as an example, you would retrieve the redirect url by doing `Input::get('redirect')`.

<a name="global-rows-per-page"></a>
### Global Rows Per Page

	/**
	 * This is the fallback value to use if the user hasn't set a custom rows per page in a model
	 *
	 * @type NULL|int
	 */
	'global_rows_per_page' => 20,

Your admin users have the ability to set the rows per page in each model with this dropdown:

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/rows-per-page.png" />

This is persistent across until the user's session expires. The `global_rows_per_page` option is the default value for when the user hasn't yet set the number they want for any particular model.