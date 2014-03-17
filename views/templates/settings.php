<form class="settings_form" data-bind="submit: save">
	<h2 data-bind="text: $root.settingsTitle"></h2>

	<!-- ko foreach: editFields -->
		<!-- ko if: $data && editable && visible -->
			<div data-bind="attr: {class: type}">
				<label data-bind="attr: {for: field_id}, text: title + ':'"></label>

			<!-- ko if: type === 'text' -->
				<div class="characters_left" data-bind="charactersLeft: {value: $root[field_name], limit: limit}"></div>
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field_name],
																			valueUpdate: 'afterkeydown', characterLimit: limit" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="text: $root[field_name]()"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'textarea' -->
				<div class="characters_left" data-bind="charactersLeft: {value: $root[field_name], limit: limit}"></div>
				<!-- ko if: editable -->
				<textarea data-bind="attr: {disabled: $root.freezeForm || !editable, id: field_id}, value: $root[field_name],
																		valueUpdate: 'afterkeydown', characterLimit: limit,
																		style: {height: height + 'px'}"></textarea>
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="text: $root[field_name]"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'wysiwyg' -->
				<!-- ko if: editable -->
					<textarea class="wysiwyg" data-bind="attr: {disabled: $root.freezeForm, id: field_id},
								wysiwyg: {value: $root[field_name], id: field_id}"></textarea>
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="html: $root[field_name]"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'markdown' -->
				<!-- ko if: editable -->
					<div class="markdown_container" data-bind="style: {height: height + 'px'}">
						<div class="characters_left" data-bind="charactersLeft: {value: $root[field_name], limit: limit}"></div>
						<textarea data-bind="attr: {disabled: $root.freezeForm, id: field_id}, characterLimit: limit,
																		value: $root[field_name], valueUpdate: 'afterkeydown'"></textarea>
						<div class="preview" data-bind="markdown: $root[field_name]"></div>
					</div>
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="markdown: $root[field_name]"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'number' -->
				<!-- ko if: editable -->
					<span class="symbol" data-bind="text: symbol"></span>
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field_name],
												number: {decimals: decimals, key: field_name, thousandsSeparator: thousands_separator,
														decimalSeparator: decimal_separator}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<span data-bind="text: symbol"></span>
					<span class="uneditable" data-bind="value: $root[field_name], number: {decimals: decimals, key: field_name,
																					thousandsSeparator: thousands_separator,
																					decimalSeparator: decimal_separator}"></span>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'bool' -->
				<!-- ko if: editable -->
					<input type="checkbox" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, bool: field_name, checked: $root[field_name]" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<span data-bind="text: $root[field_name]() ? 'yes' : 'no'"></span>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'enum' -->
				<input type="hidden" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field_name],
												select2: {data: {results: options}}" />
			<!-- /ko -->

			<!-- ko if: type === 'date' -->
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field_name],
																				datepicker: {dateFormat: date_format}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="formatDate: {dateFormat: date_format, value: $root[field_name]()}"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'time' -->
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field_name],
																			timepicker: {timeFormat: time_format}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="formatTime: {timeFormat: time_format, value: $root[field_name]()}"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'datetime' -->
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field_name],
																		datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="formatDateTime: {timeFormat: time_format, dateFormat: date_format,
																		value: $root[field_name]()}"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'image' -->
				<div class="upload_container" data-bind="attr: {id: field_id}">
					<div class="uploader" data-bind="attr: {disabled: $root.freezeForm, id: field_name + '_uploader'}, value: $root.activeItem,
											fileupload: {field: field_name, size_limit: size_limit, uploading: uploading, image: true,
														upload_percentage: upload_percentage, upload_url: upload_url}">
															<?php echo trans('administrator::administrator.uploadimage') ?></div>
					<!-- ko if: uploading -->
						<div class="uploading"
						data-bind="text: '<?php echo trans('administrator::administrator.imageuploading') ?>' + upload_percentage() + '%'"></div>
					<!-- /ko -->
				</div>

				<!-- ko if: $root[field_name] -->
					<div class="image_container">
						<img data-bind="attr: {src: file_url + '?path=' + location + $root[field_name]()}" />
						<input type="button" class="remove_button" data-bind="click: function() {$root[field_name](null)}" value="x" />
					</div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'file' -->
				<div class="upload_container" data-bind="attr: {id: field_id}">
					<div class="uploader" data-bind="attr: {disabled: $root.freezeForm, id: field_name + '_uploader'}, value: $root.activeItem,
											fileupload: {field: field_name, size_limit: size_limit, uploading: uploading,
														upload_percentage: upload_percentage, upload_url: upload_url}">
															<?php echo trans('administrator::administrator.uploadfile') ?></div>
					<!-- ko if: uploading -->
						<div class="uploading"
						data-bind="text: '<?php echo trans('administrator::administrator.fileuploading') ?>' + upload_percentage() + '%'"></div>
					<!-- /ko -->
				</div>

				<!-- ko if: $root[field_name] -->
					<div class="file_container">
						<a data-bind="attr: {href: file_url + '?path=' + location + $root[field_name](), title: $root[field_name]},
							text: $root[field_name]"></a>
						<input type="button" class="remove_button" data-bind="click: function() {$root[field_name](null)}" value="x" />
					</div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'color' -->
				<input type="text" data-type="color" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field_name]" />
				<div class="color_preview" data-bind="style: {backgroundColor: $root[field_name]}, visible: $root[field_name]"></div>
			<!-- /ko -->
			</div>
		<!-- /ko -->
	<!-- /ko -->

	<div class="control_buttons">
		<input type="submit" value="<?php echo trans('administrator::administrator.save') ?>"
			data-bind="attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />

		<!-- ko if: actions().length -->
			<!-- ko foreach: actions -->
				<!-- ko if: has_permission -->
					<input type="button" data-bind="click: function(){$root.customAction(action_name, messages, confirmation)}, value: title,
																	attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
				<!-- /ko -->
			<!-- /ko -->
		<!-- /ko -->

		<span class="message" data-bind="css: { error: statusMessageType() == 'error', success: statusMessageType() == 'success' },
										notification: statusMessage "></span>
	</div>
</form>