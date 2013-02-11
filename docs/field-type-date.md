# Field Type - Date

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-date.png" />

The `date` field type should be a DATE or DATETIME type in your database.

	'date' => array(
		'type' => 'date',
		'title' => 'Date',
		'date_format' => 'yy-mm-dd', //optional, will default to this value
	)

In the edit form, an admin user will be presented with a jQuery UI Datepicker.

The `date_format` option lets you define how the date is displayed. This uses the formatting options from [jQuery Datepicker formatDate](http://docs.jquery.com/UI/Datepicker/formatDate).

<a name="filter"></a>
## Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-date-filter.png" />

The `date` field filter comes with a start and end date. This allows you to narrow down the result set to a range, set only a minimum date, or set only a maximum date.