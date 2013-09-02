<div class="table_container">

	<div class="results_header">
		<h2 data-bind="text: modelTitle"></h2>

		<div class="actions">
			<!-- ko if: globalActions().length -->
				<!-- ko foreach: globalActions -->
					<!-- ko if: has_permission -->
						<input type="button" data-bind="click: function(){$root.customAction(false, action_name, messages, confirmation)}, value: title,
																		attr: {disabled: $root.freezeForm() || $root.freezeActions()}" />
					<!-- /ko -->
				<!-- /ko -->
			<!-- /ko -->
			<!-- ko if: actionPermissions.create -->
				<a class="new_item"
					data-bind="attr: {href: base_url + modelName() + '/new'},
								text: '<?php echo trans('administrator::administrator.new') ?> ' + modelSingle()"></a>
			<!-- /ko -->
		</div>

		<div class="action_message" data-bind="css: { error: globalStatusMessageType() == 'error', success: globalStatusMessageType() == 'success' },
										notification: globalStatusMessage "></div>
	</div>

	<div class="page_container">
		<div class="per_page">
			<input type="hidden" data-bind="value: rowsPerPage, select2: {minimumResultsForSearch: -1, data: {results: rowsPerPageOptions},
											allowClear: false}" />
			<span> <?php echo trans('administrator::administrator.itemsperpage') ?></span>
		</div>
		<div class="paginator">
			<input type="button" value="<?php echo trans('administrator::administrator.previous') ?>"
					data-bind="attr: {disabled: pagination.isFirst() || !pagination.last() || !initialized() }, click: function() {page('prev')}" />
			<input type="button" value="<?php echo trans('administrator::administrator.next') ?>"
					data-bind="attr: {disabled: pagination.isLast() || !pagination.last() || !initialized() }, click: function() {page('next')}" />
			<input type="text" data-bind="attr: {disabled: pagination.last() === 0 || !initialized() }, value: pagination.page" />
			<span data-bind="text: ' / ' + pagination.last()"></span>
		</div>
	</div>

	<table class="results" border="0" cellspacing="0" id="customers" cellpadding="0">
		<thead>
			<tr>
				<!-- ko foreach: columns -->
					<th data-bind="visible: visible, css: {sortable: sortable,
	'sorted-asc': (column_name == $root.sortOptions.field() || sort_field == $root.sortOptions.field()) && $root.sortOptions.direction() === 'asc',
	'sorted-desc': (column_name == $root.sortOptions.field() || sort_field == $root.sortOptions.field()) && $root.sortOptions.direction() === 'desc'}">
						<!-- ko if: sortable -->
							<div data-bind="click: function() {$root.setSortOptions(sort_field ? sort_field : column_name)}, text: title"></div>
						<!-- /ko -->

						<!-- ko ifnot: sortable -->
							<div data-bind="text: title"></div>
						<!-- /ko -->
					</th>
				<!-- /ko -->
			</tr>
		</thead>
		<tbody>
			<!-- ko foreach: rows -->
				<tr data-bind="click: function() {$root.clickItem($data[$root.primaryKey].raw); return true},
							css: {result: true, even: $index() % 2 == 1, odd: $index() % 2 != 1,
									selected: $data[$root.primaryKey].raw == $root.itemLoadingId()}">
					<!-- ko foreach: $root.columns -->
						<td data-bind="html: $parentContext.$data[column_name].rendered, visible: visible"></td>
					<!-- /ko -->
				</tr>
			<!-- /ko -->
		</tbody>
	</table>

	<div class="loading_rows" data-bind="visible: loadingRows">
		<div><?php echo trans('administrator::administrator.loading') ?></div>
	</div>

	<div class="no_results" data-bind="visible: pagination.last() === 0">
		<div><?php echo trans('administrator::administrator.noresults') ?></div>
	</div>
</div>

<div class="item_edit_container" data-bind="itemTransition: activeItem() !== null || loadingItem(), style: {width: expandWidth() + 'px'}">
	<div class="item_edit" data-bind="template: 'itemFormTemplate', style: {width: (expandWidth() - 27) + 'px'}"></div>
</div>