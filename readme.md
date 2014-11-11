# Laravel Administrator

Administrator is an administrative interface builder for [Laravel](http://laravel.com). With Administrator you can visually manage your Eloquent models and their relations, and also create stand-alone settings pages for storing site data and performing site tasks.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://administrator.frozennode.com/)
- **Version:** 4.14.2

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

### 4.14.2
- Bugfix: Query clauses in the newQuery method for models was throwing errors if bindings were involved

### 4.14.1
- Bugfix: when validating all rules against all attributes for existing models, "exists" and "unique" rules would cause problems

### 4.14.0
- New translations (hr, nb)
- Bugfix: The Spanish translation had an issue with noun genders
- Bugfix: Visible option wasn't previously working with filters
- Bugfix: The front-end form should now resize to its correct height at all times
- Bugfix: If relationships defined an alternate primary key on the foreign table, Administrator would always just grab the model's primary key name

### 4.13.0
- Field descriptions can now be provided in addition to the labels
- Bugfix: There were some styling issues due to applying styles at the body level
- Bugfix: Dates in time fields before 1902 were previously not possible
- Bugfix: MS opacity declaration was making it impossible to build the LESS
- Bugfix: Hungarian and Serbian languages had some errors
- Bugfix: It should now be possible to use custom validation rules and messages
- Bugfix: Model validation would previously only check the dirty attributes of existing models. It now checks everything.
- Bugfix: When setting the database.fetch config as PDO::FETCH_ASSOC, administrator would incorrectly try to access the count query results as an object