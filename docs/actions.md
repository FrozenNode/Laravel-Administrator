# Custom Actions

- [Introduction](#introduction)
- [Model Config](#model-config)
- [Settings Config](#settings-config)
- [Confirmations](#confirmations)
- [Dynamic Messages](#dynamic-messages)

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

				//return true to flash the success message
				//return false to flash the default error
				//return a string to show a custom error
				//return a Response::download() to initiate a file download
				return true;
			}
		),
	),

The `title` option lets you define the button's label value.

The `messages` option is an array with three keys: `active`, `success`, and `error`. The `active` key is what is shown to the user as the action is being performed. The `success` key is the success message. The `error` key is the default error message.

The `permission` option is an anonymous function that gets the relevant `$model` passed to it as its only parameter. This is exactly the same as if you were to put this action in your [`action_permissions`](/docs/model-configuration#action-permissions) array. Where you choose to put the permission callback is entirely up to you.

> **Note**: If you want to show a custom error message, return an error string back from the `action` function. If you want to initiate a file download, return a Response::download().

<a name="model-config"></a>
## Model Config

In a [model configuration file](/docs/model-configuration#custom-actions), the Eloquent model instance for that item will be passed into the `action` function.

	'action' => function(&$model)
	{
		//
	}

You can also create a general action on your model page in the `global_actions` array.

	'global_actions' => array(
		'some_action' => array(
			//action options
		)
	)

These global custom actions are passed the filtered query builder object so that you can do something with the current result set if you choose to do so. You can also use this to publish all unpublished items, send emails to unnotified users, or really anything you can think of.

<a name="settings-config"></a>
## Settings Config

In a [settings configuration file](/docs/settings-configuration#custom-actions), the currently-saved data for the page is passed by reference into the `action` function.

	'action' => function(&$data)
	{
		//
	}

<a name="confirmations"></a>
## Confirmations

If you want a confirmation dialog to appear before the action is performed, you can pass in a `confirmation` option for the action:

	'clear_cache' => array(
		'title' => 'Clear Cache',
		'confirmation' => 'Are you sure you want to clear the cache?',
		'action' => function(&$data)
		{
			//clear the cache
		}
	),

If the admin user confirms, the action will proceed. If they do not, the action will not.

<a name="dynamic-messages"></a>
## Dynamic Messages

It's possible to pass in anonymous functions to any of the custom action text fields (`title`, `confirmation`, and any of the `messages` keys). These anonymous functions will be passed the relevant Eloquent model or settings config object. For example:

	'ban_user' => array(
		'title' => function($model)
		{
			return "Are you sure you want to " . ($model->banned ? 'unban ' : 'ban ') . $model->name . '?';
		},
		'messages' => array(
			'active' => function($model)
			{
				return ($model->banned ? 'Unbanning ' : 'Banning ') . $model->name . '...';
			},
			'success' => function($model)
			{
				return $model->name . ($model->banned ? ' unbanned!' : ' banned!');
			},
			'error' => function($model)
			{
				return "There was an error while " . ($model->banned ? 'unbanning ' : 'banning ') . $model->name;
			},
		),
		'action' => function(&$data)
		{
			//ban the user
		}
	),