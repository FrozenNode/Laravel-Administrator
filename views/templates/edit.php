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
			{{if field.type === 'text'}}
				<div>
					<label for="edit_field_${ key }">${field.title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key]" />
				</div>
			{{/if}}
			{{if field.type === 'relation_belongs_to' || field.type === 'relation_has_one'}}
				<div>
					<label for="edit_field_${ key }">${field.title}:</label>
					<select id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, chosen: true, value: $root[key], options: field.options, 
														optionsValue: function(item) {return item.id}, 
														optionsText: function(item) {return item[field.title_field]}, 
														optionsCaption: 'None'"></select>
				</div>
			{{/if}}
			{{if field.type === 'relation_has_many' || field.type === 'relation_has_many_and_belongs_to'}}
				<div>
					<label for="edit_field_${ key }">${field.title}:</label>
					<select id="edit_field_${ key }" multiple="true" data-bind="attr: {disabled: freezeForm}, chosen: true, selectedOptions: $root[key], 
														options: field.options, 
														optionsValue: function(item) {return item.id}, 
														optionsText: function(item) {return item[field.title_field]} "></select>
				</div>
			{{/if}}
			{{if field.type === 'currency'}}
				<div class="currency">
					<label for="edit_field_${ key }">${field.title}:</label>
					<span class="symbol">${field.symbol}</span>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key], 
																		currency: {decimals: field.decimals, key: key}" />
				</div>
			{{/if}}
			{{if field.type === 'date'}}
				<div class="date">
					<label for="edit_field_${ key }">${field.title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key], datepicker: {dateFormat: field.date_format}" />
				</div>
			{{/if}}
			{{if field.type === 'time'}}
				<div class="time">
					<label for="edit_field_${ key }">${field.title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key], timepicker: {timeFormat: field.time_format}" />
				</div>
			{{/if}}
			{{if field.type === 'datetime'}}
				<div class="datetime">
					<label for="edit_field_${ key }">${field.title}:</label>
					<input type="text" id="edit_field_${ key }" data-bind="attr: {disabled: freezeForm}, value: $root[key], 
																				datetimepicker: {dateFormat: field.date_format, timeFormat: field.time_format}" />
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