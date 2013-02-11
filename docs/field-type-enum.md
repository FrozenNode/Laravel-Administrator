# Field Type - Enum

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-enum.png" />

The `enum` field type should be any text-like type or an ENUM in your database. This field type helps you narrow down the options for your admin users in a data set that you know will never change. The names of the seasons might be a good use of this field.

	'season' => array(
		'type' => 'enum',
		'title' => 'Season',
		'options' => array('Winter', 'Spring', 'Summer', 'Fall'), //must be an array
	),
	//alternate method:
	'season' => array(
		'type' => 'enum',
		'title' => 'Season',
		'options' => array(
			'Winter' => 'Cold, Cold Winter!',
			'Spring',
			'Summer' => 'Hot, Hot Summer!',
			'Fall'
		),
	),

In the edit form, an admin user will be presented with a select box showing the choices.

The `options` option lets you declare the choices that the user will see. You can either provide a simple array of strings, or if the key is a string, the key will be saved to the database while the value will be presented to the user.

<a name="filter"></a>
## Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-enum-filter.png" />

The `enum` field filter works basically the same as the edit field. A user is presented with a select box which then narrows down the result set using whatever option is chosen.