# Installation

- [Getting Administrator](#getting-administrator)
- [Administrator Config](#administrator-config)

<a name="getting-administrator"></a>
##Getting Administrator

You can either create a directory in your `/bundles` directory called `administrator` and manually copy the bundle contents into it, or you can run the artisan command:

```php
php artisan bundle:install administrator
```

Then add this to your `bundles.php` array:

```php
'administrator' => array(
	'handles' => 'admin', //this determines what URI this bundle will use
	'auto' => true,
),
```

<a name="getting-administrator"></a>
##Administrator Config

Once the bundle is installed, create a new file in your application config called administrator.php (`application/config/administrator.php`). Then copy the contents of the bundle's config file (`bundles/administrator/config/administrator.php`) and paste it into the application config file you just created. This will be where define the bundle-wide configuration options.
