# Validation

- [Introduction](#introduction)
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

<a name="using-aware"></a>
## Using Aware

If you're already using [Aware](https://github.com/awareness/aware), then you don't really have to do anything! Aware allows you to define a static `$rules` property on your Eloquent models, which works exactly like it does in Administrator.