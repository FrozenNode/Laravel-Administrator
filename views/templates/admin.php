<div class="table_container">
	<div class="results_header">
		<h2>${modelTitle}</h2>
		<div class="paginator">
			<input type="button" value="prev" data-bind="attr: {disabled: pagination.isFirst() || !pagination.last() }, click: function() {page('prev')}" />
			<input type="button" value="next" data-bind="attr: {disabled: pagination.isLast() || !pagination.last() }, click: function() {page('next')}" />
			<input type="text" data-bind="attr: {disabled: pagination.last() === 0 }, value: pagination.page" /> of ${pagination.last}
		</div>
		<a href="${base_url + modelName() + '/new'}" class="new_item">New ${modelTitle}</a>
	</div>
	<table class="results" border="0" cellspacing="0" id="customers" cellpadding="0">
		<thead>
			<tr>
				{{each(i, column) columns}}
					<th class="${sortable ? 'sortable' : ''}
									${i == sortOptions.field() || sort_field == sortOptions.field() ? 'sorted-' + sortOptions.direction() : ''}">
						{{if sortable}}
							<div data-bind="click: function() {setSortOptions(sort_field ? sort_field : i)}">${title}</div>
						{{else}}
							<div>${title}</div>
						{{/if}}
					</th>
				{{/each}}
			</tr>
		</thead>
		<tbody>
			{{each(i, row) rows}}
				<tr class="${ i % 2 == 1 ? 'even' : 'odd' } ${row[$root.primaryKey] == $root.itemLoadingId() ? 'selected' : ''} result"
							data-bind="click: function() {clickItem(row[$root.primaryKey])}">
					{{each(j, col) columns}}
						<td>${ row[j] }</td>
					{{/each}}
				</tr>
			{{/each}}
		</tbody>
	</table>

	<div class="loading_rows" data-bind="visible: loadingRows">
		<div>Loading...</div>
	</div>

	<div class="no_results" data-bind="visible: pagination.last() === 0">
		<div>No Results</div>
	</div>
</div>

<div class="item_edit_container" data-bind="itemTransition: activeItem() !== null || loadingItem()">
	<div class="item_edit" data-bind="template: 'itemFormTemplate'"></div>
</div>