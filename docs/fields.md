# Fields

- [Introduction](#introduction)
- [Title Option](#title-option)
- [Type Option](#type-option)
- [Editable Option](#editable-option)
- [Setter Option](#setter-option)
- [Visible Option](#visible-option)
- [Value Option](#value-option)
- [Filters](#filters)
- [Settings Page](#settings-page)

<a name="introduction"></a>
## Introduction

As you're [setting up your model config](/docs/model-configuration) or your [settings config](/docs/settings-configuration), you'll have to supply an `edit_fields` option. This is an array of fields that represents what will be shown to the admin user in your model or settings edit form. For models, each field should be one of your model's SQL columns or one of its [Eloquent relationships](/docs/field-type-relationship). The order in which they are given is the order in which the admin user will see them.

	/**
	 * The editable fields
	 *
	 * @type array
	 */
	'edit_fields' => array(
		'surname', //string given, assumed type 'text' with title of 'surname'
		'name' => array(
			'title' => 'Name',
		),
		'published' => array(
			'title' => 'Published',
			'type' => 'bool',
		),
		'expired' => array(
			'title' => 'Expired',
			'type' => 'bool',
			'visible' => function($model)
			{
				return $model->exists;
			}
		),
		'collection' => array(
			'type' => 'relationship',
			'title' => 'Collection',
			'name_field' => 'name',
		),
		'uri' => array(
			'title' => 'URI (leave blank for auto)',
		),
		'image' => array(
			'title' => 'Image (1423 x 441)',
			'type' => 'image',
			'naming' => 'random',
			'location' => 'public/uploads/products/originals/',
			'size_limit' => 2,
			'sizes' => array(
		 		array(1423, 441, 'crop', 'public/uploads/products/resize/', 100),
		 	)
		)
	),

 If you provide a simple string (as is done in the first field above `surname`), the default behavior is to make it a `text` field with a title of `surname`. You can also provide more options by passing in an array with an index equal to the name of the attribute or the relationship method name. There are a number of different field types (you can see them in the menu to the left), each with different options that are specific to that field type.

 There are only two universal options that apply to all edit fields and filters: `title` and `type`. There is one more boolean option that applies to all edit fields, but not to the filters: `editable`.

 > All other options are specific to certain field types. For a detailed look at all of the other options, check out the different field types in the left menu.

<a name="title-option"></a>
## Title Option

The `title` option lets you set the label of a field.

	'name' => array(
		'title' => 'Name',
	),

<a name="type-option"></a>
## Type Option

The `type` option lets you set the field's type. See the Field Types menu on the left for the complete list.

	'hex' => array(
		'title' => 'Color',
		'type' => 'color',
	),

<a name="editable-option"></a>
## Editable Option

The `editable` option determines whether or not a field can be edited. By default, this is set to `true`. Set this to false if you want to show a field to the admin user without letting them edit it.

	'unique_hash' => array(
		'title' => 'Unique Hash',
		'editable' => false,
	),

You can also pass a closure to the `editable` option whose only parameter is the Eloquent model (if it's a model page) or the settings data (if it's a settings page):

	'unique_hash' => array(
		'title' => 'Unique Hash',
		'editable' => function($model)
		{
			return !$model->exists; //will only be editable before an item is saved for the first time
		},
	),

<a name="setter-option"></a>
## Setter Option

The `setter` option lets you define a field as an attribute that is set on the Eloquent model, but is unset before the model is saved. This gives you access to use it as a [mutator](http://laravel.com/docs/eloquent#accessors-and-mutators) without having to worry about that value getting stored in the database. By default, this is set to `false` for all fields except for the [`password`](/docs/field-type-password) field.

	'name' => array(
		'title' => 'Name',
		'setter' => true,
	),

<a name="visible-option"></a>
## Visible Option

The `visible` option lets you determine if a field should be present for a particular model state. The default value of this field is boolean true. Passing in boolean false will hide the field. You can also pass in an anonymous function that accepts the relevant `$model` as the single parameter. You can return a truthy value if you want to show the field for that item, or you can return a falsey value if you hide it. This is particularly useful for hiding a field when you're creating an item or when you're editing one.

	'initial_thoughts' => array(
		'title' => 'Initial Thoughts',
		'type' => 'textarea',
		'visible' => function($model)
		{
			return !$model->exists; //will only show up before an item is saved for the first time
		},
	),

<a name="value-option"></a>
## Value Option

The `value` option lets you define a default value for a field. In a filter set this will be the default value that loads with the page. In the edit fields it will be the default value used for new items.

	'stuff' => array(
		'title' => 'Stuff',
		'type' => 'text',
		'value' => 'foo'
	),

<a name="filters"></a>
## Filters

The `filters` option in your [model config](/docs/model-configuration) lets you use certain field types as filters for your model's result set. The filterable field types are [`key`](/docs/field-type-key), [`text`](/docs/field-type-text), [`number`](/docs/field-type-number), [`bool`](/docs/field-type-bool), [`enum`](/docs/field-type-enum), [`date`](/docs/field-type-date), [`time`](/docs/field-type-time), [`datetime`](/docs/field-type-datetime), and [`relationship`](/docs/field-type-relationship). Each field type's filter works slightly different. For a detailed look at how the filter works for each field type, check out each field type's docs page in the menu on the left.

	/**
	 * The filterable fields
	 *
	 * @type array
	 */
	'filters' => array(
		'name' => array(
			'title' => 'Name',
		),
		'collection' => array(
			'type' => 'relationship',
			'title' => 'Collection',
			'name_field' => 'name',
		),
		'price' => array(
			'type' => 'number',
			'title' => 'Price',
			'symbol' => '$',
			'decimals' => 2,
		),
		'colors' => array(
			'type' => 'relationship',
			'title' => 'Colors',
			'name_field' => 'name',
		),
	),

Just as with all fields, if you provide a simple string instead of an array, the default behavior is to make it a `text` field with a title equal to the attribute name. When you provide an array of options with an index equal to the name of the attribute or the relationship method name, you can control the interface more.

You can set a default value for your filters by providing a `value` option to the filter array. For filters that have minimum and maximum input fields, you can set the `min_value` and `max_value` options.

	'filters' => array(
		'name' => array(
			'title' => 'Name',
			'value' => 'John',
		),
		'collection' => array(
			'type' => 'relationship',
			'title' => 'Collection',
			'name_field' => 'name',
			'value' => 13, //the id of the selected item
		),
		'price' => array(
			'type' => 'number',
			'title' => 'Price',
			'symbol' => '$',
			'decimals' => 2,
			'min_value' => 19.00,
			'max_value' => 255.45,
		),
		'colors' => array(
			'type' => 'relationship',
			'title' => 'Colors',
			'name_field' => 'name',
			'value' => array(3, 4), //an array of ids
		),
	),

<a name="settings-page"></a>
## Settings Page

If you're creating a settings page, you can use all of the field types except for [`key`](/docs/field-type-key) and [`relationship`](/docs/field-type-relationship).

> For a detailed description of all the settings page options, see the **[settings configuration docs](/docs/settings-configuration)**