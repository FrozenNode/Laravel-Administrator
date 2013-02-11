# Field Type - Time

- [Usage](#usage)
- [Filter](#filter)

<a name="usage"></a>
## Usage

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-time.png" />

The `time` field type should be a TIME type in your database.

	'start_time' => array(
		'type' => 'time',
		'title' => 'Start Time',
		'time_format' => 'HH:mm', //optional, will default to this value
	)

In the edit form, an admin user will be presented with a jQuery timepicker.

The `time_format` option lets you define how the time is displayed. This uses the formatting options from [jQuery timepicker](http://trentrichardson.com/examples/timepicker/#tp-formatting).

<a name="filter"></a>
## Filter

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/field-type-time-filter.png" />

The `time` field filter comes with a start and end time. This allows you to narrow down the result set to a range, set only a minimum time, or set only a maximum time.