# Validation

- [Introduction](#introduction)
- [Custom Messages](#custom-messages)
- [Using Aware](#using-aware)

<a name="introduction"></a>
## Introduction

Administrator uses [Laravel's validation](http://laravel.com/docs/validation) to validate your models. You can either provide a [`rules`](/docs/model-configuration#validation-rules) option in your configuration files:

	'rules' => array(
		'name' => 'required',
		'age' => 'required|integer|min:18',
	)

 Or for model pages, you can provide a static `$rules` property in your Eloquent models like this:

	class Movie extends Eloquent {

		/**
		 * Validation rules
		 */
		public static $rules = array(
			'name' => 'required',
			'age' => 'required|integer|min:18',
		);
	}

Now if an admin user tries to save a Movie without an age or an age below 18, Administrator will notify the user of the error and disallow the save from occurring.

<a name="custom-messages"></a>
## Custom Messages

There's a good chance that you'll need to use custom validation messages for each model that you're presenting to your users. In order to do this, you can provide a [`messages`](/docs/model-configuration#validation-messages) option in your configuration files:

	'messages' => array(
		'name.required' => 'The name field is required',
		'age.min' => 'The minimum age is 18 years old',
	)

Or for model pages, you can provide a static `$messages` property in your Eloquent models like this:

	class Movie extends Eloquent {

		/**
		 * Validation rules
		 */
		public static $messages = array(
			'name.required' => 'The name field is required',
			'age.min' => 'The minimum age is 18 years old',
		);
	}

<a name="using-aware"></a>
## Using Aware

If you're already using [Aware](https://github.com/awareness/aware), then you don't really have to do anything! Aware allows you to define a static `$rules` property on your Eloquent models, which works exactly like it does in Administrator.