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
		rows_per_page_url = "<?php echo URL::to_route('admin_rows_per_page', array($config->name)) ?>",
		route = "<?php echo $route ?>",
		csrf = "<?php echo Session::token() ?>",
		adminData = {
			primary_key: "<?php echo $primaryKey; ?>",
			<?php if ($model) {?>
				id: <?php echo $model->exists ? $model->{$model::$key} : '0'; ?>,
			<?php } ?>
			rows: <?php echo json_encode($rows) ?>,
			rows_per_page: <?php echo $config->rowsPerPage ?>,
			sortOptions: <?php echo json_encode($config->sort) ?>,
			model_name: "<?php echo $config->name ?>",
			model_title: "<?php echo $config->title ?>",
			model_single: "<?php echo $config->single ?>",
			expand_width: <?php echo $config->formWidth ?>,
			actions: <?php echo json_encode($config->actions); ?>,
			filters: <?php echo json_encode($filters); ?>,
			edit_fields: <?php echo json_encode($editFields['arrayFields']); ?>,
			data_model: <?php echo json_encode($editFields['dataModel']); ?>,
			column_model: <?php echo json_encode($config->columns['columns']); ?>,
			action_permissions: <?php echo json_encode($config->actionPermissions); ?>
		};
</script>

<style type="text/css">

	div.item_edit form.edit_form select {
		width: <?php echo $config->formWidth - 65 ?>px;
	}

	div.item_edit form.edit_form .cke {
		width: <?php echo $config->formWidth - 67 ?>px;
	}

	div.item_edit form.edit_form div.markdown textarea {
		width: <?php echo intval(($config->formWidth - 75) / 2) - 12 ?>px;
		max-width: <?php echo intval(($config->formWidth - 75) / 2) - 12 ?>px;
	}

	div.item_edit form.edit_form div.markdown div.preview {
		width: <?php echo intval(($config->formWidth - 75) / 2) ?>px;
	}

	div.item_edit form.edit_form input[type="text"], div.item_edit form.edit_form textarea {
		max-width: <?php echo $config->formWidth - 75 ?>px;
		width: <?php echo $config->formWidth - 75 ?>px;
	}

	div.item_edit form.edit_form > div.image img, div.item_edit form.edit_form > div.image div.image_container {
		max-width: <?php echo $config->formWidth - 65 ?>px;
		width: <?php echo $config->formWidth - 65 ?>px;
	}

</style>

<?php echo Form::token() ?>

<script id="adminTemplate" type="text/html">
	<?php echo View::make("administrator::templates.admin")?>
</script>

<script id="itemFormTemplate" type="text/html">
	<?php echo View::make("administrator::templates.edit")?>
</script>

<script id="filtersTemplate" type="text/html">
	<?php echo View::make("administrator::templates.filters")?>
</script>