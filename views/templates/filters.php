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