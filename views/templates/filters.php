<h2>Filters</h2>
<div class="panel_contents">
	{{each filters}}
		<div class="${type} ${minMax ? 'min_max' : ''}">
			<label for="filter_field_${ field }">${title}:</label>
		{{if type === 'key'}}
			<input type="text" id="filter_field_${ field }" data-bind="value: value, valueUpdate: 'afterkeydown'" />
		{{/if}}
		{{if type === 'text'}}
			<input type="text" id="filter_field_${ field }" data-bind="value: value, valueUpdate: 'afterkeydown'" />
		{{/if}}
		{{if type === 'number'}}
			<span class="symbol">${symbol}</span>

			<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue, number: {decimals: decimals, key: field,
																					thousandsSeparator: thousandsSeparator,
																					decimalSeparator: decimalSeparator}" />
			<span>to</span>
			<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue, number: {decimals: decimals, key: field,
																					thousandsSeparator: thousandsSeparator,
																					decimalSeparator: decimalSeparator}" />
		{{/if}}
		{{if type === 'bool'}}
			<select id="filter_field_${ field }" data-bind="value: value, chosen: true, options: ['true', 'false'],
															optionsCaption: 'All'"></select>
		{{/if}}
		{{if type === 'enum'}}
			<select id="filter_field_${ field }" data-bind="value: value, chosen: true, options: options, optionsCaption: 'All',
															optionsValue: function(item) {return item.value},
															optionsText: function(item) {return item.text}"></select>
		{{/if}}
		{{if type === 'date'}}
			<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue, datepicker: {dateFormat: date_format}" />
			<span>to</span>
			<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue, datepicker: {dateFormat: date_format}" />
		{{/if}}
		{{if type === 'time'}}
			<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue, timepicker: {timeFormat: time_format}" />
			<span>to</span>
			<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue, timepicker: {timeFormat: time_format}" />
		{{/if}}
		{{if type === 'datetime'}}
			<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue,
																	datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
			<span>to</span>
			<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue,
																	datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
		{{/if}}
		{{if type === 'belongs_to'}}
			{{if autocomplete}}
			<select id="filter_field_${ field }" data-bind="value: value, ajaxChosen: {field: field, type: 'filter'},
													options: $root.listOptions[field],
													optionsValue: function(item) {return item[column]},
													optionsText: function(item) {return item[name_field]},
													optionsCaption: 'All'"></select>
			{{else}}
			<select id="filter_field_${ field }" data-bind="value: value, chosen: true, options: $root.listOptions[field],
													optionsValue: function(item) {return item[column]},
													optionsText: function(item) {return item[name_field]},
													optionsCaption: 'All'"></select>
			{{/if}}
		{{/if}}
		{{if type === 'has_many_and_belongs_to'}}
			{{if autocomplete}}
			<select id="filter_field_${ field }" size="7" multiple="true" data-bind="ajaxChosen: {field: field, type: 'filter'},
													options: $root.listOptions[field], selectedOptions: value,
													optionsValue: function(item) {return item[foreignKey]},
													optionsText: function(item) {return item[name_field]} "></select>
			{{else}}
			<select id="filter_field_${ field }" size="7" multiple="true" data-bind="chosen: true,
													options: $root.listOptions[field], selectedOptions: value,
													optionsValue: function(item) {return item[foreignKey]},
													optionsText: function(item) {return item[name_field]} "></select>
			{{/if}}
		{{/if}}
		</div>
	{{/each}}
</div>