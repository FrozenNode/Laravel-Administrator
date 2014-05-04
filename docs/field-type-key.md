# Field Type - Key

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-key.jpg" />

The `key` field type can be used to show the primary key's value. You cannot make this field editable since primary key values are handled internally by your database.

	'id' => array(
		'type' => 'key', //optional...Administrator will know when a field is the model's key
		'title' => 'ID',
	),

<a name="filter"></a>
## Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-key-filter.jpg" />

The `key` field filter lets you type in the key of an item that you're looking for if you already know it. This is helpful if you're trying to quickly find an item that is referenced somewhere else in your database.