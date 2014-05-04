# Installation

- [Composer](#composer)
- [Laravel 3](#laravel-3)
- [Assets](#assets)
- [Administrator Config](#administrator-config)
- [Model Config](#model-config)
- [Settings Config](#settings-config)

<a name="composer"></a>
## Composer

To install Administrator as a Composer package to be used with Laravel 4, simply add administrator to your composer.json `require` key:

	"require": {
		"laravel/framework": "4.0.*",
		"frozennode/administrator": "dev-master"
	}

..and run `composer update`.  Once it's installed, you can register the service provider in `app/config/app.php` in the `providers` array:

	'providers' => array(
	    'Frozennode\Administrator\AdministratorServiceProvider',
	)


<a name="laravel-3"></a>
## Laravel 3

Since Administrator has switched over to Composer, you can no longer use `php artisan bundle:install administrator` or `php artisan bundle:upgrade administrator`. If you want to use Administrator with Laravel 3, you must switch to the [3.3.2 branch](https://github.com/FrozenNode/Laravel-Administrator/tree/3.3.2), download it, and add it in the `/bundles/administrator` directory.

After you've installed Administrator in the bundles directory, add this to your bundles.php file:

	'administrator' => array(
		'handles' => 'admin', //this determines what URI this bundle will use
		'auto' => true,
	),

<a name="assets"></a>
## Assets

After the package is installed, you need to publish the package's assets like this:

	php artisan asset:publish frozennode/administrator

It is best to publish the assets whenever Administrator updates. Instead of doing this manually, you can add the above command to your `scripts` object in your composer.json file:

	"scripts": {
		"pre-update-cmd": [
			"php artisan clear-compiled"
		],
		"post-install-cmd": [
			"php artisan optimize",
			"php artisan asset:publish frozennode/administrator"
		],
		"post-update-cmd": [
			"php artisan optimize",
			"php artisan asset:publish frozennode/administrator"
		]
	},

<a name="administrator-config"></a>
## Administrator Config

You can publish the config file with:

	php artisan config:publish frozennode/administrator

This will create the file `app/config/packages/frozennode/administrator/administrator.php` and seed it with some defaults. This [config file](http://administrator.frozennode.com/docs/configuration) is the primary way you interact with Administrator.

If you've installed the Laravel 3 bundle, you can either edit the `bundles/administrator/config/administrator.php` file directly, or you can create an `administrator.php` at `application/config`.

There are several required fields that must be supplied. Among them are the `menu` option where you define the menu structure of your site and point to your model configuration files.

> For a detailed description of all the configuration options, see the **[configuration docs](/docs/configuration)**


<a name="model-config"></a>
## Model Config

Any Eloquent model (or any object that ultimately extends from an Eloquent model) can be represented by a model configuration file. These files can be kept anywhere in your application directory structure and you provide the path to their location in the main `administrator.php` config (via the `model_config_path` option). The names of these files correspond to the values supplied in the `menu` option in the `administrator.php` config.

There are several required fields that must be supplied in order for a model config file to work. Apart from that you can also define a number of optional fields that help you customize your admin interface on a per-model basis. For instance, if one of your models needs a WYSIWYG field, you'll probably want the edit form to be wider than the default width. All you would have to do is set the `form_width` option in that model's config.

> For a detailed description of all the model configuration options, see the **[model configuration docs](/docs/model-configuration)**


<a name="settings-config"></a>
## Settings Config

Settings configuration files help you manage administrative options that aren't necessarily best represented by an Eloquent model. These files can be kept anywhere in your application directory structure and you provide the path to their location in the main `administrator.php` config (via the `settings_config_path` option). The names of these files correspond to the values supplied in the `menu` option in the `administrator.php` config.

There are several required fields that must be supplied in order for a settings config file to work. Apart from that you can also define a number of optional fields that help you customize your settings page.

> For a detailed description of all the settings configuration options, see the **[settings configuration docs](/docs/settings-configuration)**