# Custom Actions

- [Introduction](#introduction)
- [Model Config](#model-config)
- [Settings Config](#settings-config)

<a name="introduction"></a>
## Introduction

You can define custom actions in your [model](/docs/model-configuration#custom-actions) or [settings config files](/docs/settings-configuration#custom-actions) if you want to provide the administrative user buttons to perform custom code. You can modify an Eloquent model, or on a settings page you can give a user a button to clear the site cache or backup the database. A custom action is part of the `actions` array in your config files and it looks like this:

	/**
	 * This is where you can define the model's custom actions
	 */
	'actions' => array(
		//Clearing the site cache
		'clear_cache' => array(
			'title' => 'Clear Cache',
			'messages' => array(
				'active' => 'Clearing cache...',
				'success' => 'Cache cleared!',
				'error' => 'There was an error while clearing the cache',
			),
			//the settings data is passed to the function and saved if a truthy response is returned
			'action' => function(&$data)
			{
				Cache::flush();
				return true; //return true to flash the success message, false to flash the default error, and a string to show a custom error
			}
		),
	),

The `title` option lets you define the button's label value.

The `messages` option is an array with three keys: `active`, `success`, and `error`. The `active` key is what is shown to the user as the action is being performed. The `success` key is the success message. The `error` key is the default error message.

> **Note**: If you want to show a custom error message, return an error string back from the `action` function

<a name="model-config"></a>
## Model Config

In a [model configuration file](/docs/model-configuration#custom-actions), the Eloquent model instance for that item will be passed into the `action` function.

	'action' => function(&$model)
	{
		//
	}

<a name="settings-config"></a>
## Settings Config

In a [settings configuration file](/docs/settings-configuration#custom-actions), the currently-saved data for the page is passed by reference into the `action` function.

	'action' => function(&$data)
	{
		//
	}