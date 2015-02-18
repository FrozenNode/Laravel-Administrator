# Laravel Administrator

Administrator is an administrative interface builder for [Laravel](http://laravel.com). With Administrator you can visually manage your Eloquent models and their relations, and also create stand-alone settings pages for storing site data and performing site tasks.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://administrator.frozennode.com/)
- **Version:** 4.16.5

[![Build Status](https://travis-ci.org/FrozenNode/Laravel-Administrator.png?branch=master)](https://travis-ci.org/FrozenNode/Laravel-Administrator)

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />

## Composer

To install Administrator as a Composer package to be used with Laravel 4, simply add this to your composer.json:

```json
"frozennode/administrator": "dev-master"
```

..and run `composer update`.  Once it's installed, you can register the service provider in `app/config/app.php` in the `providers` array:

```php
'providers' => array(
    'Frozennode\Administrator\AdministratorServiceProvider',
)
```

Then publish the config file with `php artisan config:publish frozennode/administrator`. This will add the file `app/config/packages/frozennode/administrator/administrator.php`. This [config file](http://administrator.frozennode.com/docs/configuration) is the primary way you interact with Administrator.

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

### 4.16.5
- Bugfix: Another strange old bug where multiple search fields would do a where and instead of a where or

### 4.16.4
- Bugfix: Strange old bug when using only a setter field on a model

### 4.16.3
- Bugfix: Adding validateString back into Admin validator to avoid issues with old versions of Laravel

### 4.16.2
- Bugfix: New Illuminate validateString method did the same thing as the Administrator method of the same name

### 4.16.1
- Bugfix: Previous update broke temporary upload paths

### 4.16.0
- It's now possible to use the raw value of a file/image field to help with storing files on remote servers.

### 4.15.0
- New uneditable states for color, password, enum, and wysiwyg fields for when the editable option resolves to false
- New translations (sk)
- Bugfix: Editable fields are now also verified on the back end
- Bugfix: Setting a string image length would fail uploads
- Bugfix: Basic validation for relationship fields wasn't working
- Bugfix: "Characters left" text was sitting unnecessarily outside the "editable" conditional
- Bugfix: Some missing image-related translations
- Bugfix: Editable option wasn't working for some fields