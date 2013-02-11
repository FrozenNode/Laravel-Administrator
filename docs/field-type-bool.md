# Field Type - Bool

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

The `bool` field type should be represented as an integer field in your database. Usually schema creators allow you to choose *BOOLEAN* which resolves to something like *TINYINT(1)*. This field will work as long as you can put integer 1s and 0s in your database field.

	'is_good' => array(
		'type' => 'bool',
		'title' => 'Is Good',
	)

In the edit form, an admin user will be presented with a checkbox that looks like this:

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-bool.png" />

<a name="filter"></a>
## Filter

A `bool` field type can be used in the [`filters`](/docs/model-configuration#filters) option. When used as a filter, it will give the admin user the option to either choose true, false, or all.

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-bool-filter.png" />