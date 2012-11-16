<h2>Filters</h2>
<div class="panel_contents">
	{{each filters}}
		{{if type === 'id'}}
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
		{{if type === 'currency'}}
			<div class="currency min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				<span class="symbol">${symbol}</span>

				<input type="text" id="filter_field_min_${ field }" data-bind="value: min_value, currency: {decimals: decimals, key: field}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: max_value, currency: {decimals: decimals, key: field}" />
			</div>
		{{/if}}
		{{if type === 'date'}}
			<div class="date min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				
				<input type="text" id="filter_field_min_${ field }" data-bind="value: min_value, datepicker: {dateFormat: date_format}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: max_value, datepicker: {dateFormat: date_format}" />
			</div>
		{{/if}}
		{{if type === 'time'}}
			<div class="time min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				
				<input type="text" id="filter_field_min_${ field }" data-bind="value: min_value, timepicker: {timeFormat: time_format}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: max_value, timepicker: {timeFormat: time_format}" />
			</div>
		{{/if}}
		{{if type === 'datetime'}}
			<div class="datetime min_max">
				<label for="filter_field_min_${ field }">${title}:</label>
				
				<input type="text" id="filter_field_min_${ field }" data-bind="value: min_value, 
																		datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
				<span>to</span>
				<input type="text" id="filter_field_max_${ field }" data-bind="value: max_value, 
																		datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
			</div>
		{{/if}}
		{{if type === 'relation_belongs_to' || type === 'relation_has_one'}}
			<div>
				<label for="filter_field_${ field }">${title}:</label>
				<select id="filter_field_${ field }" data-bind="value: value, chosen: true, options: options, 
														optionsValue: function(item) {return item.id}, 
														optionsText: function(item) {return item[title_field]}, 
														optionsCaption: 'None'"></select>
			</div>
		{{/if}}
		{{if type === 'relation_has_many' || type === 'relation_has_many_and_belongs_to'}}
			<div>
				<label for="filter_field_${ field }">${title}:</label>
				<select id="filter_field_${ field }" size="7" multiple="true" data-bind="chosen: true, options: options, selectedOptions: value,
														optionsValue: function(item) {return item.id}, 
														optionsText: function(item) {return item[title_field]} "></select>
			</div>
		{{/if}}
	{{/each}}
</div>