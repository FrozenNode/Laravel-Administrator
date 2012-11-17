<h2>Filters</h2>
<div class="panel_contents">
	{{each filters}}
		{{if type === 'key'}}
			<div>
				<label for="filter_field_${ field }">${title} (numbers only):</label>
				<input type="text" id="filter_field_${ field }" data-bind="value: value, valueUpdate: 'afterkeydown'" />
			</div>
		{{/if}}
		{{if type === 'text'}}
			<div>
				<label for="filter_field_${ field }">${title}:</label>
				<input type="text" id="filter_field_${ field }" data-bind="value: value, valueUpdate: 'afterkeydown'" />
			</div>
		{{/if}}
		{{if type === 'number'}}
			<div class="number min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				<span class="symbol">${symbol}</span>

				<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue, number: {decimals: decimals, key: field}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue, number: {decimals: decimals, key: field}" />
			</div>
		{{/if}}
		{{if type === 'date'}}
			<div class="date min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				
				<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue, datepicker: {dateFormat: date_format}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue, datepicker: {dateFormat: date_format}" />
			</div>
		{{/if}}
		{{if type === 'time'}}
			<div class="time min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				
				<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue, timepicker: {timeFormat: time_format}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue, timepicker: {timeFormat: time_format}" />
			</div>
		{{/if}}
		{{if type === 'datetime'}}
			<div class="datetime min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				
				<input type="text" id="filter_field_min_${ field }" data-bind="value: minValue, 
																		datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: maxValue, 
																		datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
			</div>
		{{/if}}
		{{if type === 'belongs_to' || type === 'has_one'}}
			<div>
				<label for="filter_field_${ field }">${title}:</label>
				<select id="filter_field_${ field }" data-bind="value: value, chosen: true, options: options, 
														optionsValue: function(item) {return item.id}, 
														optionsText: function(item) {return item[name_field]}, 
														optionsCaption: 'None'"></select>
			</div>
		{{/if}}
		{{if type === 'has_many' || type === 'has_many_and_belongs_to'}}
			<div>
				<label for="filter_field_${ field }">${title}:</label>
				<select id="filter_field_${ field }" size="7" multiple="true" data-bind="chosen: true, options: options, selectedOptions: value,
														optionsValue: function(item) {return item.id}, 
														optionsText: function(item) {return item[name_field]} "></select>
			</div>
		{{/if}}
	{{/each}}
</div>