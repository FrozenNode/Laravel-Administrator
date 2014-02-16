# Columns

- [Introduction](#introduction)
- [Simple Columns](#simple-columns)
- [Column Headers](#column-headers)
- [Visible Option](#visible-option)
- [Using Accessors](#using-accessors)
- [Setting the Sort Field](#setting-the-sort-field)
- [Custom Selects](#custom-selects)
- [Relationship Columns](#relationship-columns)
- [Custom Outputs](#custom-outputs)

<a name="introduction"></a>
## Introduction

In each model's config file you must specify a `columns` option. This should be an array of the columns that you wish to display in a model's results table. Columns can be made up of any of the following:

* Attribute names on your model
* [Accessors](http://laravel.com/docs/eloquent#accessors-and-mutators)
* [Relationships](#relationship-columns) (inlcuding [nested relationships](/docs/relationship-columns#nested-relationships))

You can control the column's header through the `title` option, the `sort_field` that will be used if you sort by that columns, and any custom output you want for that column.


<a name="simple-columns"></a>
## Simple Columns

If you want to just output the value of your database table, you can do this:

	'columns' => array(
		'id',
		'name',
		'price',
	)

The values have to match the name of the column in your database. If you are using a more complex arrangement like in the next few sections, you'll need to make sure that the column's name as the *key* of your array item like this:

	'columns' => array(
		'id' => array(
			'title' => 'ID'
		),
	)

If you're deriving this column from another table or a custom select, this key will be used by Administrator as the column's alias.

> For the remainder of this page, the 'columns' => array() portion will be kept out of examples. All of the subsequent examples are supposed to be in the 'columns' array, if it's not screamingly obvious.

<a name="column-headers"></a>
## Column Headers

If you want to control the output of a column's header, set the `title` option:

	'id' => array(
		'title' => 'ID'
	)

<a name="visible-option"></a>
## Visible Option

The `visible` option lets you determine if a column should be present. The default value is boolean true. Passing in boolean false will hide the column. You can also pass in a closure that accepts the current data model as the single parameter. You can return a truthy value if you want to show the column for that user, or you can return a falsey value if you hide it.

	'secret_info' => array(
		'title' => 'Secret Info',
		'visible' => function($model)
		{
			return Auth::user()->hasRole('super_admin');
		},
	),

<a name="using-accessors"></a>
## Using Accessors

[Eloquent Accessors](http://laravel.com/docs/eloquent#accessors-and-mutators) can also be used as column values. For instance, if you have a column called `salary` and an accessor in your Eloquent model that looks like this:

	public function getFormattedSalaryAttribute()
	{
		return '$'.number_format($this->getAttribute('salary'), 2);
	}

You would be able to reference `formattted_salary` as the column's key like this:

	'formatted_salary' => array(
		'title' => 'Formatted Salary'
	)

> **Note**: You won't be able to sort accessor columns until you define a sort_field!

<a name="setting-the-sort-field"></a>
## Setting the Sort Field

If you're using an accessor, you may also want to define a `sort_field` that Administrator can use to sort it. This is required because an accessor doesn't exist on the model's database table. All you'd need to do is reference an item on that table like this:

	'formatted_salary' => array(
		'title' => 'Formatted Salary',
		'sort_field' => 'salary',
	)

<a name="unsortable-columns"></a>
## Unsortable Columns

If you want to disable sorting for a column, you can set the `sortable` option to false:

	'image' => array(
		'title' => 'Image',
		'output' => '<img src="/uploads/products/resize/(:value)" height="100" />',
		'sortable' => false,
	)

<a name="custom-selects"></a>
## Custom Selects

If you're dissatisfied with using your model's standard columns and accessors, you also have the ability to create a column as a custom `select` statement. Any valid SQL SELECT statement works here. This is useful if you want to use things like SELECT functions. This also comes with a (very) slight performance boost over using accessors since all the work is happening in the SQL.

When you define a custom `select`, you need to prefix any column in the table with `(:table).`. This is necessary because the query that is performed to get your model's result set often joins together a number of different tables. This is what a custom `select` option might look like:

	'good' => array(
		'title' => 'Is Good',
		'select' => "IF((:table).is_good, 'yes', 'no')",
	)

Here the `good` key will be the column's alias, so you can name it anything you want. If is_good is 1 for a row, it will show as 'yes'. If it is 0, it will show as 'no'.

<a name="relationship-columns"></a>
## Relationship Columns

> For a more in-depth look at relationship columns, check out the [relationship columns docs](/docs/relationship-columns)

In any moderately complex database, a table might have columns that represent an ID on another table. Most of the time it's fairly useless to display this ID to an admin user because numbers mean more to a computer than to a human. Alternatively, a relationship may not be represented on a model's table at all, but instead on a pivot table that connects two tables, or as a column on another model's table.

If you want to display related columns, you can provide a `relationship` option. The value of this option has to be *the name of the Eloquent relationshp on your model*. In addition to this, you need to provide a `select` option that Administrator will use to grab values from the relationship table. So if you have a `Director` model and you want to count the number of films he's been involved in, you could do something like this:

	'num_films' => array(
		'title' => '# Films',
		'relationship' => 'films', //this is the name of the Eloquent relationship method!
		'select' => "COUNT((:table).id)",
	)

Any SQL grouping function will work in the `select` statement. Much more is possible with the `relationship` option, so check out the [relationship columns docs](/docs/relationship-columns) for more detail.

<a name="custom-outputs"></a>
## Custom Outputs

If you want your column to show more than just text, you can use the `output` option. This can either be a string or an anonymous function.

If you provide an anonymous function, the only argument is the relevant column's value from the database. For instance, if you are using a [color](/docs/field-type-color) field and you want to clearly show the admin user what color a row is, you can do this:

	'hex' => array(
		'title' => 'Color',
		'output' => function($value)
		{
			return '<div style="background-color: ' . $value . '; width: 200px; height: 20px; border-radius: 2px;"></div>';
		},
	),

Alternatively, you can also pass a string to the `output` option and Administrator will replace the string `(:value)` with the row's value for this column.

	'hex' => array(
		'title' => 'Color',
		'output' => '<div style="background-color: (:value); width: 200px; height: 20px; border-radius: 2px;"></div>',
	),

Or maybe you're using an [image](/docs/field-type-image) field and you want to display the image in your result set. In that case you'd do:

	'banner_image' => array(
		'title' => 'Banner Image',
		'output' => '<img src="/uploads/collections/resize/(:value)" height="100" />',
	),

Custom outputs are available for all column types, not just columns that are on the model's table.