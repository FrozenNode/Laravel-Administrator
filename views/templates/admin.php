<div class="table_container">
	<div class="results_header">
		<h2 data-bind="text: modelTitle"></h2>
		<div class="paginator">
			<input type="button" value="<?php echo __('administrator::administrator.previous') ?>"
					data-bind="attr: {disabled: pagination.isFirst() || !pagination.last() }, click: function() {page('prev')}" />
			<input type="button" value="<?php echo __('administrator::administrator.next') ?>"
					data-bind="attr: {disabled: pagination.isLast() || !pagination.last() }, click: function() {page('next')}" />
			<input type="text" data-bind="attr: {disabled: pagination.last() === 0 }, value: pagination.page" />
			<span data-bind="text: ' / ' + pagination.last()"></span>
		</div>
		<div class="per_page">
			<input type="hidden" data-bind="value: rowsPerPage, select2: {minimumResultsForSearch: -1, data: {results: rowsPerPageOptions},
											allowClear: false}" />
			<span> <?php echo __('administrator::administrator.itemsperpage') ?></span>
		</div>
		<!-- ko if: actionPermissions.create -->
			<a class="new_item"
				data-bind="attr: {href: base_url + modelName() + '/new'},
							text: '<?php echo __('administrator::administrator.new') ?> ' + modelSingle()"></a>
		<!-- /ko -->
	</div>
	<table class="results" border="0" cellspacing="0" id="customers" cellpadding="0">
		<thead>
			<tr>
				<!-- ko foreach: columns -->
					<th data-bind="css: {sortable: sortable,
		'sorted-asc': (field == $root.sortOptions.field() || sort_field == $root.sortOptions.field()) && $root.sortOptions.direction() === 'asc',
		'sorted-desc': (field == $root.sortOptions.field() || sort_field == $root.sortOptions.field()) && $root.sortOptions.direction() === 'desc'}">
						<!-- ko if: sortable -->
							<div data-bind="click: function() {$root.setSortOptions(sort_field ? sort_field : field)}, text: title"></div>
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
				<tr data-bind="click: function() {$root.clickItem($data[$root.primaryKey]); return true},
							css: {result: true, even: $index() % 2 == 1, odd: $index() % 2 != 1,
									selected: $data[$root.primaryKey] == $root.itemLoadingId()}">
					<!-- ko foreach: $root.columns -->
						<td data-bind="html: $parentContext.$data[field]"></td>
					<!-- /ko -->
				</tr>
			<!-- /ko -->
		</tbody>
	</table>

	<div class="loading_rows" data-bind="visible: loadingRows">
		<div><?php echo __('administrator::administrator.loading') ?></div>
	</div>

	<div class="no_results" data-bind="visible: pagination.last() === 0">
		<div><?php echo __('administrator::administrator.noresults') ?></div>
	</div>
</div>

<div class="item_edit_container" data-bind="itemTransition: activeItem() !== null || loadingItem(), style: {width: expandWidth() + 'px'}">
	<div class="item_edit" data-bind="template: 'itemFormTemplate', style: {width: (expandWidth() - 27) + 'px'}"></div>
</div>