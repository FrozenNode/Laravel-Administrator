# Laravel Administrator

Administrator is a database interface package for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is reference your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 4.1.0

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />

## Composer

To install Administrator as a Composer package to be used with Laravel 4, simply add this to your composer.json:

```json
"frozennode/administrator": "dev-master"
```

..and run `composer install`.  Once it is installed, you can register the service provider in `app/config/app.php` in the `providers` array:

```php
'providers' => array(
    'Frozennode\Administrator\AdministratorServiceProvider',
)
```

Then publish the config file with `php artisan config:publish frozennode/administrator`. This will add the file `app/config/packages/frozennode/administrator/administrator.php`. This [config file](http://administrator.frozennode.com/docs/configuration) is the primary way you interact with Administrator.

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

### 4.1.0
- If you select multiple BelongsToMany relationship filter options, the list will search for items that has all the selected relationships. Previously this was an OR
- Bugfix: Formatted date filters were not being properly sent to SQL
- Bugfix: Null values for unrequired relationships weren't resetting field
- Bugfix: Stray old "Admin\\Libraries" sitting in the Column model was causing issues with relationship fields
- Bugfix: Column objects weren't indexing properly when a column was simply a string value
- Bugfix: BelongsTo edit fields weren't setting due to overwriting with an empty array
- Bugfix: Custom actions in settings weren't working properly
- Bugfix: relationship saving was causing overload issue in php 5.4


See *changelog.md* for the changelog from previous versions