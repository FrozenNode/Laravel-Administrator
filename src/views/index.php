<div id="admin_page" class="with_sidebar">
	<div id="sidebar">
		<div class="panel sidebar_section" id="filters_sidebar_section" data-bind="template: 'filtersTemplate'"></div>
	</div>
	<div id="content" data-bind="template: 'adminTemplate'"></div>
</div>

<script type="text/javascript">
	var site_url = "<?php echo Url::to('/') ?>",
		base_url = "<?php echo $baseUrl ?>/",
		asset_url = "<?php echo $assetUrl ?>",
		file_url = "<?php echo URL::route('admin_display_file', array($config->getOption('name'))) ?>",
		rows_per_page_url = "<?php echo URL::route('admin_rows_per_page', array($config->getOption('name'))) ?>",
		route = "<?php echo $route ?>",
		csrf = "<?php echo Session::token() ?>",
		language = "<?php echo Config::get('app.locale') ?>",
		adminData = {
			primary_key: "<?php echo $primaryKey ?>",
			<?php if ($itemId !== null) {?>
				id: <?php echo $itemId ?>,
			<?php } ?>
			rows: <?php echo json_encode($rows) ?>,
			rows_per_page: <?php echo $dataTable->getRowsPerPage() ?>,
			sortOptions: <?php echo json_encode($dataTable->getSort()) ?>,
			model_name: "<?php echo $config->getOption('name') ?>",
			model_title: "<?php echo $config->getOption('title') ?>",
			model_single: "<?php echo $config->getOption('single') ?>",
			expand_width: <?php echo $formWidth ?>,
			actions: <?php echo json_encode($actions) ?>,
			global_actions: <?php echo json_encode($globalActions) ?>,
			filters: <?php echo json_encode($filters) ?>,
			edit_fields: <?php echo json_encode($arrayFields) ?>,
			data_model: <?php echo json_encode($dataModel) ?>,
			column_model: <?php echo json_encode($columnModel) ?>,
			action_permissions: <?php echo json_encode($actionPermissions) ?>,
			languages: <?php echo json_encode(trans('administrator::knockout')) ?>
		};
</script>

<style type="text/css">

	div.item_edit form.edit_form select, div.item_edit form.edit_form input[type=hidden], div.item_edit form.edit_form .select2-container {
		width: <?php echo $formWidth - 59 ?>px !important;
	}

	div.item_edit form.edit_form .cke {
		width: <?php echo $formWidth - 67 ?>px !important;
	}

	div.item_edit form.edit_form div.markdown textarea {
		width: <?php echo intval(($formWidth - 75) / 2) - 12 ?>px !important;
		max-width: <?php echo intval(($formWidth - 75) / 2) - 12 ?>px !important;
	}

	div.item_edit form.edit_form div.markdown div.preview {
		width: <?php echo intval(($formWidth - 75) / 2) ?>px !important;
	}

	div.item_edit form.edit_form input[type="text"], div.item_edit form.edit_form input[type="password"], div.item_edit form.edit_form textarea {
		max-width: <?php echo $formWidth - 75 ?>px !important;
		width: <?php echo $formWidth - 75 ?>px !important;
	}

	div.item_edit form.edit_form > div.image img, div.item_edit form.edit_form > div.image div.image_container {
		max-width: <?php echo $formWidth - 65 ?>px;
	}

</style>

<?php echo Form::token() ?>

<script id="adminTemplate" type="text/html">
	<?php echo View::make("administrator::templates.admin")?>
</script>

<script id="itemFormTemplate" type="text/html">
	<?php echo View::make("administrator::templates.edit", array('config' => $config))?>
</script>

<script id="filtersTemplate" type="text/html">
	<?php echo View::make("administrator::templates.filters")?>
</script>