<div data-bind="visible: loadingItem" class="loading">Loading...</div>

<form class="edit_form" data-bind="visible: !loadingItem(), submit: saveItem">
	<h2 data-bind="text: $root[$root.primaryKey]() ? 'Edit' : 'Create New'"></h2>

	<!-- ko if: $root[$root.primaryKey]() -->
		<!-- ko if: $root.itemLink() -->
			<a class="item_link" target="_blank" data-bind="attr: {href: $root.itemLink()}">View Item</a>
		<!-- /ko -->

		<div class="key">
			<label>ID:</label>
			<span data-bind="text: $root[$root.primaryKey]"></span>
		</div>
	<!-- /ko -->

	<!-- ko foreach: editFields -->
		<!-- ko if: $data && ( $root[$root.primaryKey]() || editable ) -->
			<div data-bind="attr: {class: type}">
				<label data-bind="attr: {for: field_id}, text: title + ':'"></label>

			<!-- ko if: type === 'text' -->
				<div class="characters_left" data-bind="charactersLeft: {value: $root[field], limit: limit}"></div>
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																			valueUpdate: 'afterkeydown', characterLimit: limit" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div data-bind="text: $root[field]()"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'textarea' -->
				<div class="characters_left" data-bind="charactersLeft: {value: $root[field], limit: limit}"></div>
				<!-- ko if: editable -->
				<textarea data-bind="attr: {disabled: $root.freezeForm || !editable, id: field_id}, value: $root[field],
																		valueUpdate: 'afterkeydown', characterLimit: limit,
																		style: {height: height + 'px'}"></textarea>
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div data-bind="text: $root[field]()"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'wysiwyg' -->
				<textarea data-bind="attr: {disabled: $root.freezeForm, id: field_id}, wysiwyg: $root[field]"></textarea>
			<!-- /ko -->

			<!-- ko if: type === 'markdown' -->
				<div class="markdown_container" data-bind="style: {height: height + 'px'}">
					<div class="characters_left" data-bind="charactersLeft: {value: $root[field], limit: limit}"></div>
					<textarea data-bind="attr: {disabled: $root.freezeForm, id: field_id}, characterLimit: limit,
																	value: $root[field], valueUpdate: 'afterkeydown'"></textarea>
					<div class="preview" data-bind="markdown: $root[field]"></div>
				</div>
			<!-- /ko -->

			<!-- ko if: type === 'belongs_to' -->
				<div class="loader" data-bind="visible: loadingOptions"></div>

				<!-- ko if: autocomplete -->
				<select data-bind="attr: {disabled: $root.freezeForm() || loadingOptions(), id: field_id}, value: $root[field],
													ajaxChosen: {field: field, type: 'edit'},
													options: $root.listOptions[field],
													optionsValue: function(item) {return item[column]},
													optionsText: function(item) {return item[name_field]},
													optionsCaption: 'None'"></select>
				<!-- /ko -->

				<!-- ko ifnot: autocomplete -->
				<select data-bind="attr: {disabled: $root.freezeForm() || loadingOptions(), id: field_id}, value: $root[field], chosen: true,
													options: $root.listOptions[field],
													optionsValue: function(item) {return item[column]},
													optionsText: function(item) {return item[name_field]},
													optionsCaption: 'None'"></select>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'has_many_and_belongs_to' -->
				<div class="loader" data-bind="visible: loadingOptions"></div>

				<!-- ko if: autocomplete -->
				<select multiple="true" data-bind="attr: {disabled: $root.freezeForm() || loadingOptions(), id: field_id},
													ajaxChosen: {field: field, type: 'edit'},
													selectedOptions: $root[field], options: $root.listOptions[field],
													optionsValue: function(item) {return item[foreignKey]},
													optionsText: function(item) {return item[name_field]} "></select>
				<!-- /ko -->

				<!-- ko ifnot: autocomplete -->
				<select multiple="true" data-bind="attr: {disabled: $root.freezeForm() || loadingOptions(), id: field_id}, chosen: true,
													selectedOptions: $root[field], options: $root.listOptions[field],
													optionsValue: function(item) {return item[foreignKey]},
													optionsText: function(item) {return item[name_field]} "></select>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'number' -->
				<span class="symbol" data-bind="text: symbol"></span>
				<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																	number: {decimals: decimals, key: field,
																			thousandsSeparator: thousandsSeparator,
																			decimalSeparator: decimalSeparator}" />
			<!-- /ko -->

			<!-- ko if: type === 'bool' -->
				<input type="checkbox" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, bool: field, checked: $root[field]" />
			<!-- /ko -->

			<!-- ko if: type === 'enum' -->
				<select data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field], chosen: true, options: options,
																optionsValue: function(item) {return item.value},
																optionsText: function(item) {return item.text},
																optionsCaption: 'None'"></select>
			<!-- /ko -->

			<!-- ko if: type === 'date' -->
				<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																			datepicker: {dateFormat: date_format}" />
			<!-- /ko -->

			<!-- ko if: type === 'time' -->
				<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																			timepicker: {timeFormat: time_format}" />
			<!-- /ko -->

			<!-- ko if: type === 'datetime' -->
				<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																			datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
			<!-- /ko -->

			<!-- ko if: type === 'image' -->
				<div class="image_upload_container" data-bind="attr: {id: field_id}">
					<div class="uploader" data-bind="attr: {disabled: $root.freezeForm, id: field + '_uploader'}, value: $root.activeItem,
											imageupload: {field: field, size_limit: size_limit, uploading: uploading,
															upload_percentage: upload_percentage, upload_url: upload_url}">Upload Image</div>
					<!-- ko if: uploading -->
						<div class="uploading" data-bind="text: 'Image Uploading' + upload_percentage() + '%'"></div>
					<!-- /ko -->
				</div>

				<!-- ko if: $root[field] -->
					<div class="image_container">
						<img data-bind="attr: {src: display_url + $root[field]()}" />
						<input type="button" class="remove_button" data-bind="click: function() {$root[field](null)}" value="x" />
					</div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'color' -->
				<input type="text" data-type="color" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field]" />
				<div class="color_preview" data-bind="style: {backgroundColor: $root[field]}, visible: $root[field]"></div>
			<!-- /ko -->
			</div>
		<!-- /ko -->
	<!-- /ko -->

	<!-- ko if: $root[$root.primaryKey]() && actions.length -->
		<div class="custom_buttons">
			<!-- ko foreach: actions -->
				<!-- ko if: hasPermission -->
					<input type="button" data-bind="click: function(){$root.customAction(name, messages)}, value: title,
																	attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
				<!-- /ko -->
			<!-- /ko -->
		</div>
	<!-- /ko -->

	<div class="control_buttons">
		<!-- ko if: $root[$root.primaryKey]() -->
			<input type="button" value="Close" data-bind="click: closeItem, attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />

			<!-- ko if: actionPermissions.delete -->
				<input type="button" value="Delete" data-bind="click: deleteItem, attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- /ko -->

			<!-- ko if: actionPermissions.update -->
				<input type="submit" value="Save" data-bind="attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- /ko -->
		<!-- /ko -->

		<!-- ko ifnot: $root[$root.primaryKey]() -->
			<input type="button" value="Cancel" data-bind="click: closeItem, attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- ko if: actionPermissions.create -->
				<input type="submit" value="Create" data-bind="attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- /ko -->
		<!-- /ko -->
		<span class="message" data-bind="css: { error: statusMessageType() == 'error', success: statusMessageType() == 'success' },
										notification: statusMessage "></span>
	</div>
</form>