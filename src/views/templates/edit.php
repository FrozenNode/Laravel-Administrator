<div data-bind="visible: loadingItem" class="loading"><?php echo trans('administrator::administrator.loading') ?></div>

<form class="edit_form" data-bind="visible: !loadingItem(), submit: saveItem">
	<h2 data-bind="text: $root[$root.primaryKey]() ? '<?php echo trans('administrator::administrator.edit') ?>' :
		'<?php echo trans('administrator::administrator.createnew') ?>'"></h2>

	<!-- ko if: $root[$root.primaryKey]() -->
		<!-- ko if: $root.itemLink() -->
			<a class="item_link" target="_blank" data-bind="attr: {href: $root.itemLink()},
										text: '<?php echo trans('administrator::administrator.viewitem', array('single' => $config->single)) ?>'"></a>
		<!-- /ko -->
	<!-- /ko -->

	<!-- ko foreach: editFields -->
		<!-- ko if: $data && ( $root[$root.primaryKey]() || editable ) && visible -->
			<div data-bind="attr: {class: type}">
				<label data-bind="attr: {for: field_id}, text: title + ':'"></label>

			<!-- ko if: type === 'key' -->
				<span data-bind="text: $root[$root.primaryKey]"></span>
			<!-- /ko -->

			<!-- ko if: type === 'text' -->
				<div class="characters_left" data-bind="charactersLeft: {value: $root[field], limit: limit}"></div>
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																			valueUpdate: 'afterkeydown', characterLimit: limit" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="text: $root[field]()"></div>
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
					<div class="uneditable" data-bind="text: $root[field]"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'wysiwyg' -->
				<!-- ko if: editable -->
					<textarea data-bind="attr: {disabled: $root.freezeForm, id: field_id}, wysiwyg: $root[field]"></textarea>
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="html: $root[field]"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'markdown' -->
				<!-- ko if: editable -->
					<div class="markdown_container" data-bind="style: {height: height + 'px'}">
						<div class="characters_left" data-bind="charactersLeft: {value: $root[field], limit: limit}"></div>
						<textarea data-bind="attr: {disabled: $root.freezeForm, id: field_id}, characterLimit: limit,
																		value: $root[field], valueUpdate: 'afterkeydown'"></textarea>
						<div class="preview" data-bind="markdown: $root[field]"></div>
					</div>
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="markdown: $root[field]"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'belongs_to' -->
				<div class="loader" data-bind="visible: loadingOptions"></div>

				<!-- ko if: autocomplete -->
				<input type="hidden" data-bind="attr: {disabled: $root.freezeForm() || loadingOptions() || !editable, id: field_id},
													value: $root[field], select2Remote: {field: field, type: 'edit', constraints: constraints}" />
				<!-- /ko -->

				<!-- ko ifnot: autocomplete -->
				<input type="hidden" data-bind="attr: {disabled: $root.freezeForm() || loadingOptions() || !editable, id: field_id},
											value: $root[field], select2: {data:{results: $root.listOptions[field]}}" />
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'has_many_and_belongs_to' -->
				<div class="loader" data-bind="visible: loadingOptions"></div>

				<!-- ko if: autocomplete -->
				<input type="hidden" data-bind="attr: {disabled: $root.freezeForm() || loadingOptions() || !editable, id: field_id},
									select2Remote: {field: field, type: 'edit', multiple: true, constraints: constraints, sort: sort_field},
									value: $root[field]" />
				<!-- /ko -->

				<!-- ko ifnot: autocomplete -->
				<input type="hidden" data-bind="attr: {disabled: $root.freezeForm() || loadingOptions() || !editable, id: field_id},
													select2: {data:{results: $root.listOptions[field]}, multiple: true, sort: sort_field},
													value: $root[field]" />
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'number' -->
				<!-- ko if: editable -->
					<span class="symbol" data-bind="text: symbol"></span>
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
												number: {decimals: decimals, key: field, thousandsSeparator: thousandsSeparator,
														decimalSeparator: decimalSeparator}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<span data-bind="text: symbol"></span>
					<span class="uneditable" data-bind="value: $root[field], number: {decimals: decimals, key: field,
																					thousandsSeparator: thousandsSeparator,
																					decimalSeparator: decimalSeparator}"></span>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'bool' -->
				<!-- ko if: editable -->
					<input type="checkbox" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, bool: field, checked: $root[field]" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<span data-bind="text: $root[field]() ? 'yes' : 'no'"></span>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'enum' -->
				<input type="hidden" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
												select2: {data: {results: options}}"></select>
			<!-- /ko -->

			<!-- ko if: type === 'date' -->
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																				datepicker: {dateFormat: date_format}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="formatDate: {dateFormat: date_format, value: $root[field]()}"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'time' -->
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																			timepicker: {timeFormat: time_format}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="formatTime: {timeFormat: time_format, value: $root[field]()}"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'datetime' -->
				<!-- ko if: editable -->
					<input type="text" data-bind="attr: {disabled: $root.freezeForm, id: field_id}, value: $root[field],
																		datetimepicker: {dateFormat: date_format, timeFormat: time_format}" />
				<!-- /ko -->
				<!-- ko ifnot: editable -->
					<div class="uneditable" data-bind="formatDateTime: {timeFormat: time_format, dateFormat: date_format,
																		value: $root[field]()}"></div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'image' -->
				<div class="upload_container" data-bind="attr: {id: field_id}">
					<div class="uploader" data-bind="attr: {disabled: $root.freezeForm, id: field + '_uploader'}, value: $root.activeItem,
											fileupload: {field: field, size_limit: size_limit, uploading: uploading, image: true,
														upload_percentage: upload_percentage, upload_url: upload_url}">
															<?php echo trans('administrator::administrator.uploadimage') ?></div>
					<!-- ko if: uploading -->
						<div class="uploading"
						data-bind="text: '<?php echo trans('administrator::administrator.imageuploading') ?>' + upload_percentage() + '%'"></div>
					<!-- /ko -->
				</div>

				<!-- ko if: $root[field] -->
					<div class="image_container">
						<img data-bind="attr: {src: file_url + '?path=' + location + $root[field]()}" />
						<input type="button" class="remove_button" data-bind="click: function() {$root[field](null)}" value="x" />
					</div>
				<!-- /ko -->
			<!-- /ko -->

			<!-- ko if: type === 'file' -->
				<div class="upload_container" data-bind="attr: {id: field_id}">
					<div class="uploader" data-bind="attr: {disabled: $root.freezeForm, id: field + '_uploader'}, value: $root.activeItem,
											fileupload: {field: field, size_limit: size_limit, uploading: uploading,
														upload_percentage: upload_percentage, upload_url: upload_url}">
															<?php echo trans('administrator::administrator.uploadfile') ?></div>
					<!-- ko if: uploading -->
						<div class="uploading"
						data-bind="text: '<?php echo trans('administrator::administrator.fileuploading') ?>' + upload_percentage() + '%'"></div>
					<!-- /ko -->
				</div>

				<!-- ko if: $root[field] -->
					<div class="file_container">
						<a data-bind="attr: {href: file_url + '?path=' + location + $root[field](), title: $root[field]},
							text: $root[field]"></a>
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
				<!-- ko if: hasPermission && $root.actionPermissions[name] !== false -->
					<input type="button" data-bind="click: function(){$root.customAction(name, messages)}, value: title,
																	attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
				<!-- /ko -->
			<!-- /ko -->
		</div>
	<!-- /ko -->

	<div class="control_buttons">
		<!-- ko if: $root[$root.primaryKey]() -->
			<input type="button" value="<?php echo trans('administrator::administrator.close') ?>"
				data-bind="click: closeItem, attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />

			<!-- ko if: actionPermissions.delete -->
				<input type="button" value="<?php echo trans('administrator::administrator.delete') ?>"
					data-bind="click: deleteItem, attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- /ko -->

			<!-- ko if: actionPermissions.update -->
				<input type="submit" value="<?php echo trans('administrator::administrator.save') ?>"
					data-bind="attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- /ko -->
		<!-- /ko -->

		<!-- ko ifnot: $root[$root.primaryKey]() -->
			<input type="button" value="<?php echo trans('administrator::administrator.cancel') ?>"
				data-bind="click: closeItem, attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- ko if: actionPermissions.create -->
				<input type="submit" value="<?php echo trans('administrator::administrator.create') ?>"
					data-bind="attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
			<!-- /ko -->
		<!-- /ko -->
		<span class="message" data-bind="css: { error: statusMessageType() == 'error', success: statusMessageType() == 'success' },
										notification: statusMessage "></span>
	</div>
</form>