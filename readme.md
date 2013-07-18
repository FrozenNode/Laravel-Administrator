# Laravel Administrator

Administrator is a database interface package for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is reference your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 4.3.0

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

### 4.3.0
- Unit testing
- A fourth basic action permission is now available: 'view'. This dictates whether or not the admin user can click an item to open it
- There is now an optional 'rules' property in model configuration files which works just like the $rules static property in Eloquent models
- You can now define where the raw settings data is stored by providing a 'storage_path' option to settings configs
- You can now supply a 'confirmation' string option to your custom actions which will require a confirmation from the admin user before the action can go through
- The active item now updates itself when you perform a custom action or when you save an item
- You can now specify an options_sort_field and an options_sort_direction for relationship fields that use accessors as name fields, and as such require ordering on something other than the name_field
- 'logout_path' option is now available in the main config. By default this is false, but if you provide a string value it will show a logout button and link the user to that path if clicked
- Bugfix: Tons of other bugs that I caught while creating the unit tests :D
- Bugfix: The model results no longer require an ajax load on pageload
- Bugfix: Table prefixes are now taken into consideration
- Bugfix: Number fields would take two tries to clear
- Bugfix: Saving empty number field would result in 0
- Bugfix: Using an accessor for a name_field in a relationship field would previously cause SQL errors