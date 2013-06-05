<h2><?php echo trans('administrator::administrator.filters') ?></h2>
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
			<input type="hidden" data-bind="value: value, attr: {id: field_id}, select2: {data: {results: $root.boolOptions}}" />
		<!-- /ko -->

		<!-- ko if: type === 'enum' -->
			<input type="hidden" data-bind="value: value, attr: {id: field_id}, select2: {data: {results: options}}" />
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
			<input type="hidden" data-bind="value: value, attr: {id: field_id}, select2Remote: {field: field, type: 'filter', filterIndex: $index()}"/>
			<!-- /ko -->
			<!-- ko ifnot: autocomplete -->
			<input type="hidden" data-bind="value: value, attr: {id: field_id}, select2: {data: {results: $root.listOptions[field]}}" />
			<!-- /ko -->
		<!-- /ko -->

		<!-- ko if: type === 'has_many_and_belongs_to' -->
			<div class="loader" data-bind="visible: loadingOptions"></div>

			<!-- ko if: autocomplete -->
			<input type="hidden" size="7" data-bind="select2Remote: {field: field, type: 'filter', multiple: true, filterIndex: $index()},
													attr: {id: field_id}, value: value" />
			<!-- /ko -->
			<!-- ko ifnot: autocomplete -->
			<input type="hidden" size="7" multiple="true" data-bind="select2: {data:{results: $root.listOptions[field]}, multiple: true},
													attr: {id: field_id}, value: value" />
			<!-- /ko -->
		<!-- /ko -->
		</div>
	<!-- /ko -->
</div>