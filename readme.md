# Laravel Administrator

Administrator is an administrative interface builder for [Laravel](http://laravel.com). With Administrator you can visually manage your Eloquent models and their relations, and also create stand-alone settings pages for storing site data and performing site tasks.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://administrator.frozennode.com/)
- **Version:** 5.0.1

[![Build Status](https://travis-ci.org/FrozenNode/Laravel-Administrator.png?branch=master)](https://travis-ci.org/FrozenNode/Laravel-Administrator)

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />

## Composer

To install Administrator as a Composer package to be used with Laravel 5, simply add this to your composer.json:

```json
"frozennode/administrator": "5.*"
```

..and run `composer update`.  Once it's installed, you can register the service provider in `config/app.php` in the `providers` array:

```php
'providers' => [
	'Frozennode\Administrator\AdministratorServiceProvider',
]
```

Then publish Administrator's assets with `php artisan vendor:publish`. This will add the file `config/administrator.php`. This [config file](http://administrator.frozennode.com/docs/configuration) is the primary way you interact with Administrator. This command will also publish all of the assets, views, and translation files.

### Laravel 4

If you want to use Administrator with Laravel 4, you need to resolve to Administrator 4:

```json
"frozennode/administrator": "4.*"
```

Then publish the config file with `php artisan config:publish frozennode/administrator`. This will add the file `app/config/packages/frozennode/administrator/administrator.php`.

Then finally you need to publish the package's assets with the `php artisan asset:publish frozennode/administrator` command.

### Laravel 3

Since Administrator has switched over to Composer, you can no longer use `php artisan bundle:install administrator` or `php artisan bundle:upgrade administrator`. If you want to use Administrator with Laravel 3, you must switch to the [3.3.2 branch](https://github.com/FrozenNode/Laravel-Administrator/tree/3.3.2), download it, and add it in the `/bundles/administrator` directory and add this to your bundles.php file:

```php
'administrator' => array(
	'handles' => 'admin', //this determines what URI this bundle will use
	'auto' => true,
),
```

## Documentation

The complete docs for Administrator can be found at http://administrator.frozennode.com. You can also find the docs in the `/src/docs` directory.


## Copyright and License
Administrator was written by Jan Hartigan of Frozen Node for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.


## Recent Changelog

### 5.0.1
- Bugfix: Fixing csrf token mismatches for some requests

### 5.0.0
- Upgraded to Laravel 5
- New translations (az)