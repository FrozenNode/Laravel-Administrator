# Localization

- [Introduction](#introduction)
- [Setting Up Languages](#setting-up-languages)
- [Changing Languages](#changing-languages)
- [Localization in Administrator Config File](#localization-in-administrator-config-file)
- [Localization in Model Config Files](#localization-in-model-config-files)
- [Available Languages](#available-languages)
- [Contributing / Adding More Languages](#contributing)
- [Plugins Used](#plugins-used)

<a name="introduction"></a>
## Introduction

Localization is fully supported in Administrator. For the moment there are only a few languages, but [it's really easy to add more](#contributing). In addition to the basic localization built into Administrator, you can use localization in your config files. This works by using Laravel's localization. Administrator ensures that your `administrator.php` config file and your model config files are loaded after your languages are loaded, so there should be no problem using either the `__('some.item')` or the `Lang::line('some.item')->get()` syntax anywhere in your config files.

<a name="setting-up-languages"></a>
## Setting Up Languages

Administrator uses Laravel's built-in localization support, so all you have to do in order to localize Administrator is to either change your default language or add additional languages in your `application/config/application.php` file.

**Changing the default language**

	'language' => 'de',

By default, this value is `en`.

**Adding more languages**

	'languages' => array('en', 'de', 'hu'),

By default, this is an empty `array()`.

Check the [available languages](#available-languages) to see if your language is supported.

<a name="changing-languages"></a>
## Changing Languages

If you provide more than one valid value in the application config's `languages` array, the admin user will be presented with a language selector at the top right of the admin interface:

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/localization.png" />

You'll notice that Administrator uses Laravel's default language URI scheme. Since Administrator checks for the default language prior to building these items, this should flow smoothly with the rest of your site even if you don't use the default language URIs.

<a name="localization-in-administrator-config-file"></a>
## Localization in Administrator Config File

Setting up localization in the main `application/config/administrator.php` file is a breeze. For example, if you want to localize the title of the administrative interface:

	/**
	 * Page title
	 *
	 * @type string
	 */
	'title' => __('administrator.title'),

You can set up the language files however you like, but in this case, you'd have to set up an `administrator.php` file in (for example) `application/language/en`, `application/language/de`, or any language that you want to use. That file would look like this:

	return array(
		"title"       => "Admin",
	);

This is simply [Laravel's localization](http://laravel.com/docs/localization), so there's nothing new here!

The only exception comes when you want to set up localized values for the `menu` config option. For example, in your `application/config/administrator.php` file, you might have:

	'menu' => array(
		'E-Commerce' => array('some_model', 'some_other_model'),
	),

If you want to localize `E-Commerce`, you will have to ensure that the value you use is a string. Laravel's `__()` helper function actually returns a Lang object that converts to a string, but when used as an array index, you must call the `->get()` method on the value returned from the `__()` helper. For example:

	'menu' => array(
		__('administrator.ecommerce_title')->get() => array('some_model', 'some_other_model'),
	),

`->get()` ensures that the value is a string, so that there are no array index errors.

<a name="localization-in-model-config-files"></a>
## Localization in Model Config Files

There are no special exceptions in the model config files...so you can localize using any of the standard Laravel localization syntaxes!

<a name="available-languages"></a>
## Available Languages

Administrator currently supports the following languages:

> de en es eu hu nl pl tr

If you don't see the language you want, [contributing a new language is crazy easy](#contributing)!

<a name="contributing"></a>
## Contributing / Adding More Languages

Administrator's language files are located in the bundle's `language` directory. Each language currently requires two files: `administrator.php` and `knockout.php`. If you want to add a new language, first you should check out the [contributing section of the docs](/docs/contributing). You simply need to create those two files in the language directory that you want to add and submit a pull request. If you're not feeling up to forking and issuing a pull request on GitHub, no worries! You can simply [create a new issue](https://github.com/FrozenNode/Laravel-Administrator/issues) and write the translations in there. We'll make sure that they get added to the `language` directory.

<a name="plugins-used"></a>
## Plugins Used

Administrator uses several plugins that themselves have extensive localization support. Administrator will automatically try to match the localization to your provided Laravel languages, but it's possible that some of the plugins don't fully support your language. You can check the language support for the following plugins:

**[CKEditor](http://ckeditor.com/)**
Used in the wysiwyg field type. Directory in Administrator: `/public/js/ckeditor/lang/`

**[jQueryUI DatePicker](http://jqueryui.com/datepicker/)**
Used in date field type. Directory in Administrator: `/public/js/jquery/i18n/`

**[jQuery TimePicker](http://jonthornton.github.com/jquery-timepicker/)**
Used in time field type. Folder in Administrator: `/public/js/jquery/localization/`

**[Plupload](http://www.plupload.com/)**
Used for file/image uploads. Folder in Administrator: `/public/js/plupload/js/i18n/`
