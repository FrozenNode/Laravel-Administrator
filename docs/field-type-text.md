# Field Type - Text

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-text.png" />

The `text` field type should be any text-like type in your database. `text` is the default field type, so setting the `type` property isn't required.

	'name' => array(
		'type' => 'text', //optional, default is 'text'
		'title' => 'Name',
		'limit' => 30, //optional, defaults to no limit
	)

In the edit form, an admin user will be presented with a simple text input.

The `limit` option lets you set a character limit for the field.

<a name="filter"></a>
## Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-text-filter.png" />

The `text` field filter lets you search for items that match a given string in that field.