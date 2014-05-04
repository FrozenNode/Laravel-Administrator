# Relationship Columns

- [Introduction](#introduction)
- [Setting Up the Eloquent Relationship](#setting-up-the-eloquent-relationship)
- [Simple Select](#simple-select)
- [More Complex Selects](#more-complex-selects)
- [Nested Relationships](#nested-relationships)

<a name="introduction"></a>
## Introduction

> **Note**: This article is just about relationship columns. For a more in-depth look at all column options, check out [the columns docs](/docs/columns)

In any moderately complex database, a table might have columns that represent an ID on another table. Most of the time it's fairly useless to display this ID to an admin user because numbers mean more to a computer than to a human. Alternatively, a relationship may not be represented on a model's table at all, but instead on a pivot table that connects two tables, or as a column on another model's table.

If you want to display related columns, you can provide a `relationship` option. The value of this option has to be *the name of the Eloquent relationshp on your model*. In addition to this, you need to provide a `select` option that Administrator will use to grab values from the relationship table.

<a name="setting-up-the-eloquent-relationship"></a>
## Setting Up the Eloquent Relationship

The [Eloquent relationship](http://laravel.com/docs/eloquent#relationships) should be set up normally using the relationship method. This would look something like this:

	class User extends Eloquent {

		public function phone()
		{
			return $this->hasOne('Phone');
		}
	}

In this case, the relationship "name" that we will want to reference is `phone` (the name of the method). Another example might look like:

	class Director extends Eloquent {

		public function films()
		{
			return $this->belongsToMany('Film');
		}
	}

In this case, the relationship "name" that we will want to reference is `films`.

Administrator will respect any conditional filters you have on your relationships. This is especially relevant for `hasMany` or `belongsToMany` relationships. For example, if you set up your relationship like this:

	public function alerts()
	{
		return $this->hasMany('Alert')->whereNotified(false); //only gets unnotified alerts
	}

And you use a select to count the number of alerts, it will only count those where the `notified` column is `0`.

<a name="simple-select"></a>
## Simple Select

A simple `select` statement would be used when the data that you're joining is necessarily just one row long. This happens when the relationship is defined as a `belongsTo` or `hasOne` relationship. So let's pretend that you have a `hats` table represented by the `Hat` model. Each hat is owned by a single `User`, so there is a `user_id` column on the `hats` table. If you are displaying the `Hat` model in Administrator, you could display the hat's owner's email address in a column by doing this:

	'user_email' => array(
		'title' => "Owner's Email",
		'relationship' => 'user', //this is the name of the Eloquent relationship method!
		'select' => "(:table).email",
	)

If you want to display the user's first and last name, you could do this:

	'user_name' => array(
		'title' => "Owner's Name",
		'relationship' => 'user', //this is the name of the Eloquent relationship method!
		'select' => "CONCAT((:table).first_name, ' ', (:table).last_name)",
	)

<a name="more-complex-selects"></a>
## More Complex Selects

If want to show data from a `hasMany` or `belongsToMany` relationship, you may want to provide a grouping function in your `select` statement. If you have a `Director` model and you want to count the number of films he's been involved in, you could do something like this:

	'num_films' => array(
		'title' => '# Films',
		'relationship' => 'films', //this is the name of the Eloquent relationship method!
		'select' => "COUNT((:table).id)",
	)

If you are in your `Film` model and you want to show a formatted total of all the box office revenue, you could do this:

	'box_office' => array(
		'title' => 'Box Office',
		'relationship' => 'boxOffice', //this is the name of the Eloquent relationship method!
		'select' => "CONCAT('$', FORMAT(SUM((:table).revenue), 2))",
	)

As long as you provide a valid SQL SELECT statement into the `select` option, you have a lot of power to display your columns however you like.

<a name="nested-relationships"></a>
## Nested Relationships

Sometimes you might want to display a column value of a distantly-related model. In particular, when you have a series of `belongsTo` relationships. Imagine, for example, that you have a `cart` table. On it you have `inventory_id`, which points to the `inventory` table. The `inventory` table has `product_id`, which points to the `products` table. Your models migth look like:

### Cart Model
	public function inventory()
	{
		return $this->belongsTo('Inventory');
	}

### Inventory Model
	public function product()
	{
		return $this->belongsTo('Product');
	}

Each product has a name and we want to select it for rows in our `cart` admin. In order to do this, you can use the dot syntax that [Eloquent uses when eager loading nested relationships](http://laravel.com/docs/eloquent#eager-loading).

	'product_name' => array(
		'title' => 'Product Name',
		'relationship' => 'inventory.product',
		'select' => '(:table).name', //would select products.name
	)

There is no limit to this nesting, so if you wanted to get the product's category name, you could do:

	'category_name' => array(
		'title' => 'Category Name',
		'relationship' => 'inventory.product.category',
		'select' => '(:table).name', //would select categories.name
	)