# Fields

- [Introduction](#introduction)
- [Title Option](#title-option)
- [Type Option](#type-option)
- [Filters](#filters)

<a name="introduction"></a>
## Introduction

As you're [setting up your model's config](/docs/model-configuration), you'll have to create an `edit_fields` option for your model. This is an array of fields that represents what will be shown to the admin user in your model's edit form. Each field can either be one of your model's attributes or one of its relationships. The order in which they are given is the order in which the admin user will see them.

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

 There are only two universal options: `title` and `type`. For a detailed look at all of the other options, check out the different field types in the left menu.

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

 <a name="filters"></a>
## Filters

The `filters` option in your [model's config](/docs/model-configuration) lets you use certain field types as filters for your model's result set. The filterable field types are [`key`](/docs/field-type-key), [`text`](/docs/field-type-text), [`number`](/docs/field-type-number), [`bool`](/docs/field-type-bool), [`enum`](/docs/field-type-enum), [`date`](/docs/field-type-date), [`time`](/docs/field-type-time), [`datetime`](/docs/field-type-datetime), and [`relationship`](/docs/field-type-relationship). Each field type's filter works slightly different. For a detailed look at how the filter works for each field type, check out each field type's docs page in the menu on the left.

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