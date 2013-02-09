<h2><?php echo __('administrator::administrator.filters') ?></h2>
<div class="panel_contents">

	<!-- ko foreach: $root.filters -->
		<div data-bind="attr: {class: type + ' ' + (minMax ? 'min_max' : '')}">
			<label data-bind="attr: {for: field_id}, text: title + ':'"></label>

		<!-- ko if: type === 'key' -->
			<input type="text" data-bind="value: value, valueUpdate: 'afterkeydown', attr: {id: field_id}" />
		<!-- /ko -->

		<!-- ko if: type === 'text' -->
			<input type="text" data-bind="value: value, valueUpdate: 'afterkeydown', attr: {id: field_id}" />
		<!-- /ko -->

		<!-- ko if: type === 'color' -->
			<input type="text" data-bind="value: value, valueUpdate: 'afterkeydown', attr: {id: field_id}" />
		<!-- /ko -->

		<!-- ko if: type === 'number' -->
			<span class="symbol" data-bind="text: symbol"></span>

			<input type="text" data-bind="value: minValue, attr: {id: field_id + '_min'}, number: {decimals: decimals, key: field,
																					thousandsSeparator: thousandsSeparator,
																					decimalSeparator: decimalSeparator}" />
			<span>-</span>
			<input type="text" data-bind="value: maxValue, attr: {id: field_id + '_max'}, number: {decimals: decimals, key: field,
																					thousandsSeparator: thousandsSeparator,
																					decimalSeparator: decimalSeparator}" />
		<!-- /ko -->

		<!-- ko if: type === 'bool' -->
			<select data-bind="value: value, attr: {id: field_id}, chosen: true, options: ['true', 'false'],
															optionsCaption: '<?php echo __('administrator::administrator.all') ?>'"></select>
		<!-- /ko -->

		<!-- ko if: type === 'enum' -->
			<select data-bind="value: value, attr: {id: field_id}, chosen: true, options: options,
										optionsCaption: '<?php echo __('administrator::administrator.all') ?>',
										optionsValue: function(item) {return item.value},
										optionsText: function(item) {return item.text}"></select>
		<!-- /ko -->

		<!-- ko if: type === 'date' -->
			<input type="text" data-bind="value: minValue, attr: {id: field_id + '_min'}, datepicker: {dateFormat: date_format}" />
			<span>-</span>
			<input type="text" data-bind="value: maxValue, attr: {id: field_id + '_max'}, datepicker: {dateFormat: date_format}" />
		<!-- /ko -->

		<!-- ko if: type === 'time' -->
			<input type="text" data-bind="value: minValue, attr: {id: field_id + '_min'}, timepicker: {timeFormat: time_format}" />
			<span>-</span>
			<input type="text" data-bind="value: maxValue, attr: {id: field_id + '_max'}, timepicker: {timeFormat: time_format}" />
		<!-- /ko -->

		<!-- ko if: type === 'datetime' -->
			<input type="text" data-bind="value: minValue, attr: {id: field_id + '_min'},
																	datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
			<span>-</span>
			<input type="text" data-bind="value: maxValue, attr: {id: field_id + '_max'},
																	datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
		<!-- /ko -->

		<!-- ko if: type === 'belongs_to' -->
			<div class="loader" data-bind="visible: loadingOptions"></div>

			<!-- ko if: autocomplete -->
			<select data-bind="value: value, attr: {id: field_id}, ajaxChosen: {field: field, type: 'filter'},
													options: $root.listOptions[field],
													optionsValue: function(item) {return item.id},
													optionsText: function(item) {return item[name_field]},
													optionsCaption: '<?php echo __('administrator::administrator.all') ?>'"></select>
			<!-- /ko -->
			<!-- ko ifnot: autocomplete -->
			<select data-bind="value: value, attr: {id: field_id}, chosen: true, options: $root.listOptions[field],
													optionsValue: function(item) {return item.id},
													optionsText: function(item) {return item[name_field]},
													optionsCaption: '<?php echo __('administrator::administrator.all') ?>'"></select>
			<!-- /ko -->
		<!-- /ko -->

		<!-- ko if: type === 'has_many_and_belongs_to' -->
			<div class="loader" data-bind="visible: loadingOptions"></div>

			<!-- ko if: autocomplete -->
			<select size="7" multiple="true" data-bind="ajaxChosen: {field: field, type: 'filter'}, attr: {id: field_id},
													options: $root.listOptions[field], selectedOptions: value,
													optionsValue: function(item) {return item.id},
													optionsText: function(item) {return item[name_field]} "></select>
			<!-- /ko -->
			<!-- ko ifnot: autocomplete -->
			<select size="7" multiple="true" data-bind="chosen: true, attr: {id: field_id},
													options: $root.listOptions[field], selectedOptions: value,
													optionsValue: function(item) {return item.id},
													optionsText: function(item) {return item[name_field]} "></select>
			<!-- /ko -->
		<!-- /ko -->
		</div>
	<!-- /ko -->
</div>