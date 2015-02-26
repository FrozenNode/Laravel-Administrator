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

Localization is fully supported in Administrator. For the moment there are only a few languages, but [it's really easy to add more](#contributing). In addition to the basic localization built into Administrator, you can use localization in your administrator config files. This works by using Laravel's localization, so there should be no problem using either the `trans('some.item')` or the `Lang::get('some.item')` syntax anywhere in your config files.

<a name="setting-up-languages"></a>
## Setting Up Languages

Administrator uses Laravel's built-in localization support, so all you have to do in order to localize Administrator is to change the default local in your `app/config/app.php` file.

**Changing the default language**

	'locale' => 'de',

By default, this value is `en`.

**Adding more languages**

In Laravel 3, it was possible to provide an array of accepted languages in your `application.php` config file. In L4 this feature was removed, so the `languages` array has been moved to Administrator's config file (`app/config/packages/frozennode/administrator/administrator.php`) and is now called `locales`.

	'locales' => array('en', 'de', 'hu'),

By default, this is an empty `array()`.

Check the [available languages](#available-languages) to see if your language is supported.

<a name="changing-languages"></a>
## Changing Languages

If you provide more than one valid value in the administrator config's `locales` array, the admin user will be presented with a language selector at the top right of the admin interface:

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/localization.png" />

Since Administrator checks for the default language prior to building these items, this should flow smoothly with the rest of your site even if you don't use the default language URIs.

<a name="localization-in-administrator-config-file"></a>
## Localization in Administrator Config File

Setting up localization in the `app/config/packages/frozennode/administrator/administrator.php` file is a breeze. For example, if you want to localize the title of the administrative interface:

	/**
	 * Page title
	 *
	 * @type string
	 */
	'title' => trans('administrator.title'),

You can set up the language files however you like, but in this case, you'd have to set up an `administrator.php` file in (for example) `app/lang/en`, `app/lang/de`, or any language that you want to use. That file would look like this:

	return array(
		"title"       => "Admin",
	);

This is simply [Laravel's localization](http://laravel.com/docs/localization), so there's nothing new here!

<a name="localization-in-model-config-files"></a>
## Localization in Model Config Files

There are no special exceptions in the model config files...so you can localize using any of the standard Laravel localization syntaxes!

<a name="available-languages"></a>
## Available Languages

Administrator currently supports the following languages:

> ar az bg ca da de en es eu fi fr hr hu it ja nb nl pl pt pt-BR ru se si sk sr tr uk vi zh-CN zh-TW

If you don't see the language you want, [contributing a new language is crazy easy](#contributing)!

<a name="contributing"></a>
## Contributing / Adding More Languages

Administrator's language files are located in the package's `src/lang` directory. Each language currently requires two files: `administrator.php` and `knockout.php`. If you want to add a new language, first you should check out the [contributing section of the docs](/docs/contributing). You simply need to create those two files in the language directory that you want to add and submit a pull request. If you're not feeling up to forking and issuing a pull request on GitHub, no worries! You can simply [create a new issue](https://github.com/FrozenNode/Laravel-Administrator/issues) and write the translations in there. We'll make sure that they get added to the `lang` directory.

<a name="plugins-used"></a>
## Plugins Used

Administrator uses several plugins that themselves have extensive localization support. Administrator will automatically try to match the localization to your provided Laravel languages, but it's possible that some of the plugins don't fully support your language. You can check the language support for the following plugins:

**[CKEditor](http://ckeditor.com/)**
Used in the wysiwyg field type. Directory in Administrator: `/public/js/ckeditor/lang/`

**[jQueryUI DatePicker](http://jqueryui.com/datepicker/)**
Used in date field type. Directory in Administrator: `/public/js/jquery/i18n/`

**[jQuery TimePicker](http://jonthornton.github.com/jquery-timepicker/)**
Used in time field type. Directory in Administrator: `/public/js/jquery/localization/`

**[Plupload](http://www.plupload.com/)**
Used for file/image uploads. Directory in Administrator: `/public/js/plupload/js/i18n/`
