# Field Type - Color

- [Usage](#usage)

<a name="usage"></a>
## Usage

The `color` field type should be a VARCHAR or TEXT field in your database.

	'hex' => array(
		'type' => 'color',
		'title' => 'Color',
	)

In the edit form, an admin user will be presented with a color picker which will fill text field with a hex value starting with a # symbol.

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-color.png" />