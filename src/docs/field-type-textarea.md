# Field Type - Textarea

- [Usage](#usage)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-textarea.png" />

The `textarea` field type should be any text-like type in your database.

	'name' => array(
		'type' => 'textarea',
		'title' => 'Name',
		'limit' => 300, //optional, defaults to no limit
		'height' => 130, //optional, defaults to 100
	)

In the edit form, an admin user will be presented with a textarea.

The `limit` option lets you set a character limit for the field.

The `height` option lets you set the height of the textarea in pixels.