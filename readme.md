# Laravel Administrator

Administrator is an administrative interface builder for [Laravel](http://laravel.com). With Administrator you can visually manage your Eloquent models and their relations, and also create stand-alone settings pages for storing site data and performing site tasks.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://administrator.frozennode.com/)
- **Version:** 4.12.0

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


## Changelog

### 4.12.1
- Bugfix: Revert Merge #601 and adds back in select2 for filter booleans due to 3 needed options True, False, Both
- Bugfix: Fix Js for non numeric ids

### 4.12.0
- Added CKEditor to composer to keep updated
- Added Scrollable Data Table ranther than dropping the columns
- Added the use of checkboxes for boolean filters
- Added the ability to create select2 translation files
- Added Arabic Translation files
- Added Bulgarian Translation files
- Added Finnish Translation files
- Added Turkish Translation files
- Added Danish timepicker translation file
- Bugfix: Fixed the routing order for File routes
- BugFix: Fixed the German Translation file
- Bugfix: Freeze actions while image uploading is in progress added to keep people from clicking off the page while image is uploading
- Bugfix: Call resizePage() after getting ajax response to fix with the page sizing and not being able to see the full datatable
- Bugfix: Replace isSoftDeleting with issset(...->runSoftDelete) for Laravel 4.2 support

### 4.11.2
- Bugfix: Strange legacy issue that was never caught with passing a no-op to array_get

### 4.11.1
- Bugfix: There was a routing issue with the new non-numeric model ids

### 4.11.0
- Model ids no longer have to be numeric
- New translations (vi)
