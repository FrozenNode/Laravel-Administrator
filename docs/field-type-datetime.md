# Field Type - Datetime

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-datetime.png" />

The `datetime` field type should be a DATETIME type in your database.

	'start_time' => array(
		'type' => 'datetime',
		'title' => 'Start Time',
		'date_format' => 'yy-mm-dd', //optional, will default to this value
		'time_format' => 'HH:mm', 	 //optional, will default to this value
	)

In the edit form, an admin user will be presented with a jQuery datetimepicker.

The `date_format` option lets you define how the date is displayed. This uses the formatting options from [jQuery Datepicker formatDate](http://docs.jquery.com/UI/Datepicker/formatDate).

The `time_format` option lets you define how the time is displayed. This uses the formatting options from [jQuery timepicker](http://trentrichardson.com/examples/timepicker/#tp-formatting).

<a name="filter"></a>
## Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-datetime-filter.png" />

The `datetime` field filter comes with a start and end datetime. This allows you to narrow down the result set to a range, set only a minimum datetime, or set only a maximum datetime.