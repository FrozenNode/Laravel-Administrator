# Field Type - Key

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-key.jpg" />

The `key` field type shouldn't be used in the `edit_fields` array because managing an item's key should be handled internally by Eloquent. It will always display automatically for an existing item as an uneditable field, so you don't have to set it.

	'id' => array(
		'type' => 'key', //optional...Administrator will know when a field is the model's key
		'title' => 'ID',
	),

<a name="filter"></a>
## Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-key-filter.jpg" />

The `key` field filter lets you type in the key of an item that you're looking for if you already know it. This is helpful if you're trying to quickly find an item that is referenced somewhere else in your database.