# Installation

- [Getting Administrator](#getting-administrator)
- [Administrator Config](#administrator-config)
- [Model Config](#model-config)

<a name="getting-administrator"></a>
## Getting Administrator

To get Administrator, you can either create a directory in your `/bundles` directory called `administrator` and manually copy the bundle contents into it, or you can run the artisan command:

	php artisan bundle:install administrator

Then add this to your `bundles.php` array:

	'administrator' => array(
		'handles' => 'admin', //this determines what URI this bundle will use
		'auto' => true,
	),

<a name="administrator-config"></a>
## Administrator Config

Once the bundle is installed, create a new file in your application config called administrator.php (`application/config/administrator.php`). Then copy the contents of the bundle's config file (`bundles/administrator/config/administrator.php`) and paste it into the application config file you just created. This will be where define the bundle-wide configuration options.

There are several required fields that must be supplied. Among them are the `menu` option where you define the menu structure of your site and point to your model configuration files.

> For a detailed description of all the configuration options, see the **[configuration docs](/docs/configuration)**


<a name="model-config"></a>
## Model Config

Any Eloquent model (or any object that ultimately extends from an Eloquent model) can be represented by a model configuration file. These files can be kept anywhere in your application directory structure and you provide the path to their location in the main `administrator.php` config. The names of these files correspond to the values supplied in the `menu` option in the `administrator.php` config.

There are several required fields that must be supplied in order for a model config file to work. Apart from that you can also define a number of optional fields that help you customize your admin interface on a per-model basis. For instance, if one of your models needs a WYSIWYG field, you'll probably want the edit form to be wider than the default width. All you would have to do is set the `form_width` option in that model's config.

> For a detailed description of all the model configuration options, see the **[model configuration docs](/docs/model-configuration)**