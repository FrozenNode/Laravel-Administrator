<div data-bind="visible: loadingItem" class="loading">Loading...</div>

<div class="edit_form" data-bind="visible: !loadingItem()">
	<h2 data-bind="text: $root[$root.primaryKey]() ? 'Edit' : 'Create New'"></h2>

	{{if $root[$root.primaryKey]()}}
	<div>
		<label>ID:</label>
		<span>${$root[$root.primaryKey]}</span>
	</div>
	{{/if}}

	{{each(key, field) editFields}}
		{{if key !== $root.primaryKey}}
			{{if type === 'text'}}
				<div>
					<label for="edit_field_${ key }">${title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key]" />
				</div>
			{{/if}}
			{{if type === 'belongs_to'}}
				<div>
					<label for="edit_field_${ key }">${title}:</label>
					{{if autocomplete}}
					<select id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key],
														ajaxChosen: {field: field, type: 'edit'},
														options: $root.listOptions[field],
														optionsValue: function(item) {return item.id},
														optionsText: function(item) {return item[name_field]},
														optionsCaption: 'None'"></select>
					{{else}}
					<select id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key], chosen: true,
														options: $root.listOptions[field],
														optionsValue: function(item) {return item.id},
														optionsText: function(item) {return item[name_field]},
														optionsCaption: 'None'"></select>
					{{/if}}
				</div>
			{{/if}}
			{{if type === 'has_many_and_belongs_to'}}
				<div>
					<label for="edit_field_${ key }">${title}:</label>
					{{if autocomplete}}
					<select id="edit_field_${ key }" multiple="true" data-bind="attr: {disabled: freezeForm},
														ajaxChosen: {field: field, type: 'edit'},
														selectedOptions: $root[key], options: $root.listOptions[field],
														optionsValue: function(item) {return item.id},
														optionsText: function(item) {return item[name_field]} "></select>
					{{else}}
					<select id="edit_field_${ key }" multiple="true" data-bind="attr: {disabled: freezeForm}, chosen: true,
														selectedOptions: $root[key], options: $root.listOptions[field],
														optionsValue: function(item) {return item.id},
														optionsText: function(item) {return item[name_field]} "></select>
					{{/if}}
				</div>
			{{/if}}
			{{if type === 'number'}}
				<div class="number">
					<label for="edit_field_${ key }">${title}:</label>
					<span class="symbol">${symbol}</span>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key],
																		number: {decimals: decimals, key: key,
																				thousandsSeparator: thousandsSeparator,
																				decimalSeparator: decimalSeparator}" />
				</div>
			{{/if}}
			{{if type === 'date'}}
				<div class="date">
					<label for="edit_field_${ key }">${title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key],
																				datepicker: {dateFormat: date_format}" />
				</div>
			{{/if}}
			{{if type === 'time'}}
				<div class="time">
					<label for="edit_field_${ key }">${title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key],
																				timepicker: {timeFormat: time_format}" />
				</div>
			{{/if}}
			{{if type === 'datetime'}}
				<div class="datetime">
					<label for="edit_field_${ key }">${title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key],
																				datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
				</div>
			{{/if}}
		{{/if}}
	{{/each}}

	<div class="control_buttons">
		{{if $root[$root.primaryKey]()}}
			<input type="button" value="Close" data-bind="click: closeItem, attr: {disabled: freezeForm}" />
			<input type="button" value="Delete" data-bind="click: deleteItem, attr: {disabled: freezeForm}" />
			<input type="button" value="Save" data-bind="click: saveItem, attr: {disabled: freezeForm}" />
		{{else}}
			<input type="button" value="Cancel" data-bind="click: closeItem, attr: {disabled: freezeForm}" />
			<input type="button" value="Create" data-bind="click: saveItem, attr: {disabled: freezeForm}" />
		{{/if}}
		<span class="message" data-bind="css: { error: statusMessageType() == 'error', success: statusMessageType() == 'success' },
										notification: statusMessage "></span>
	</div>
</div>