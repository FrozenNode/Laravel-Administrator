# Settings Configuration

- [Introduction](#introduction)
- [Examples](#examples)
- [Options](#options)

<a name="introduction"></a>
## Introduction

Sometimes you want to be able to create settings pages for your administrators. Settings pages, like [the pages that display Eloquent models](/docs/model-configuration), are represented by configuration files. These files can be kept anywhere in your application directory structure so long as you provide the path to their location in the main `administrator.php` config with the [`settings_config_path`](/docs/configuration#settings-config-path) option. The names of these files correspond to the values supplied in the [`menu`](/docs/configuration#menu) option in the `administrator.php` config.

> **Note**: These are also the uris for each settings page in the admin interface.

There are several required fields that must be supplied in order for a settings config file to work, and there are several optional fields. [See the list of options below](#options).

Settings are saved as JSON files in the storage subdirectory `administrator_settings`. However, before a settings page is saved in one of these JSON files, the data is first passed through validation using the [`rules`](#validation-rules) that may exist, and then to the [`before_save`](#before-save) function where you can run any extra validation and store it however you like (e.g. write it to the database or PHP config files) for your own use within your app.

<a name="examples"></a>
## Examples

For some example config files, check out the `/examples` directory on [Administrator's GitHub repo](https://github.com/FrozenNode/Laravel-Administrator/tree/master/examples).

<a name="options"></a>
## Options

Below is a list of all the available options for settings pages. Required options are marked as *(required)*:

- [Title](#title) *(required)*
- [Edit Fields](#edit-fields) *(required)*
- [Validation Rules](#validation-rules)
- [Before Save](#before-save)
- [Permission](#permission)
- [Custom Actions](#custom-actions)
- [Storage Path](#storage-path)

<a name="title"></a>
### Title *(required)*

	/**
	 * Settings page title
	 *
	 * @type string
	 */
	'title' => 'Site Settings',

This is the title of the settings page used in the menu and as the page's primary title.

<a name="edit-fields"></a>
### Edit Fields *(required)*

	/**
	 * The edit fields array
	 *
	 * @type array
	 */
	'edit_fields' => array(
		'site_name' => array(
			'title' => 'Name',
			'type' => 'text',
		),
		'page_cache_lifetime' => array(
			'title' => 'Page Cache Lifetime (in minutes)',
			'type' => 'number',
		),
		'logo' => array(
			'title' => 'Image (200 x 150)',
			'type' => 'image',
			'naming' => 'random',
			'location' => 'public/uploads/config/logo/originals/',
			'size_limit' => 2,
			'sizes' => array(
		 		array(200, 150, 'crop', 'public/uploads/config/logo/resize/', 100),
		 	)
		)
	),

The `edit_fields` array lets you define the editable fields for a settings page. All field types are allowed with the exception of key and relationship fields. This works much like the [`edit_fields`](/docs/model-configuration#edit-fields) option in model config files. When an admin chooses to save a settings page, an array will be provided to the [`before_save callback`](#before-save) containing all of your data. The indexes of the data values will be the same as the indexes you provide in the `edit_fields` array.

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/settings-overview.png" />

> For a detailed description of all the edit field types and options, see the **[field docs](/docs/fields)**

<a name="validation-rules"></a>
### Validation Rules

	/**
	 * The validation rules for the form, based on the Laravel validation class
	 *
	 * @type array
	 */
	'rules' => array(
		'site_name' => 'required|max:50',
		'site_email' => 'required|email',
	),

The validation rules for your settings page can be set using the `rules` option. Administrator uses [Laravel's validation](http://laravel.com/docs/validation) to validate your models. If the form is invalid, it will notify the admin without saving the form.

<a name="before-save"></a>
### Before Save

	/**
	 * This is run prior to saving the JSON form data
	 *
	 * @type function
	 * @param array		$data
	 *
	 * @return string (on error) / void (otherwise)
	 */
	'before_save' => function(&$data)
	{
		if (today_is_tuesday())
		{
			return "Sorry, site settings can't be saved on Tuesday";
		}

		$data['site_name'] = $data['site_name'] . ' - The Blurst Site Ever';
	},

The `before_save` callback is run after basic validation using the [`rules`](#validation-rules) option, but before the form data is saved to the JSON storage. You can use this function to store the data however you want. Since the `$data` parameter is passed by reference, you can also manipulate the form data prior to it being saved.

The `$data` parameter is a simple array of `key -> value` pairs. The keys are the same as those provided in the [`edit_fields`](#edit-fields) option.

<a name="permission"></a>
## Permission

	/**
	 * The permission option is an authentication check that lets you define a closure that should return true if the current user
	 * is allowed to view this settings page. Any "falsey" response will result in a 404.
	 *
	 * @type closure
	 */
	'permission'=> function()
	{
		return Auth::user()->has_role('developer');
	},

The permission option lets you define a closure that determines whether or not the current user can access this settings page. If this field is provided (it isn't required), the user will only be given access if this resolves to a truthy value. If this fails, the user will be given a 404.

<a name="custom-actions"></a>
## Custom Actions

	/**
	 * This is where you can define the settings page's custom actions
	 */
	'actions' => array(
		//Ordering an item up
		'clear_page_cache' => array(
			'title' => 'Clear Page Cache',
			'messages' => array(
				'active' => 'Clearing cache...',
				'success' => 'Page Cache Cleared',
				'error' => 'There was an error while clearing the page cache',
			),
			//the settings data is passed to the closure and saved if a truthy response is returned
			'action' => function(&$data)
			{
				Cache::forget('pages');

				return true;
			}
		),

		//Fetch spot prices from a data API
		'get_spot_prices' => array(...),
	),

You can define custom actions for your settings page if you want to provide the administrative user buttons to perform custom code. In the above example, there will be two buttons produced that look like this:

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/custom-actions-settings.png" />

When the user clicks on either button, the `action` property for that button is called. The currently-saved settings data is passed into the function by reference with the `$data` parameter. This means that you can change the data however you like prior to it being saved in the JSON.

> For a detailed description of custom actions, see the **[actions docs](/docs/actions)**.

<a name="storage-path"></a>
### Storage Path

	/**
	 * The storage path in which to save the raw settings data
	 *
	 * @type string
	 */
	'storage_path' => storage_path() . '/my_custom_directory',

You can optionally provide a `storage_path` option that will determine in which directory the raw settings data will be saved. If you've saving the data via other means in the `before_save` callback, or if you're comfortable with the default location (`/app/storage/administrator_settings`), you can omit this option.