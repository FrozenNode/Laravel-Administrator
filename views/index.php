<div id="admin_page" class="with_sidebar">
	<div id="sidebar">
		<div class="panel sidebar_section" id="filters_sidebar_section" data-bind="template: 'filtersTemplate'"></div>
	</div>
	<div id="content" data-bind="template: 'adminTemplate'"></div>
</div>

<script type="text/javascript">
	var base_url = "<?php echo $baseUrl ?>/",
		route = "/<?php echo $bundleHandles ?>/",
		csrf = "<?php echo Session::token() ?>",
		adminData = {
			primary_key: "<?php echo $primaryKey; ?>",
			<?php if ($model) {?>
				id: <?php echo $model->exists ? $model->{$model::$key} : '0'; ?>,
			<?php } ?>
			rows: <?php echo json_encode($rows) ?>,
			sortOptions: <?php echo json_encode($sort) ?>,
			model_name: "<?php echo $modelName ?>",
			model_title: "<?php echo $modelTitle ?>",
			model_single: "<?php echo $modelSingle ?>",
			expand_width: <?php echo $expandWidth ?>,
			filters: <?php echo json_encode($filters); ?>,
			edit_fields: <?php echo json_encode($editFields); ?>,
			data_model: <?php echo json_encode($dataModel); ?>,
			column_model: <?php echo json_encode($columns); ?>
		};
</script>

<style type="text/css">

	div.item_edit div.edit_form select {
		width: <?php echo $expandWidth - 65 ?>px;
	}

	div.item_edit div.edit_form .cke {
		width: <?php echo $expandWidth - 67 ?>px;
	}

	div.item_edit div.edit_form div.markdown textarea {
		width: <?php echo intval(($expandWidth - 75) / 2) - 12 ?>px;
		max-width: <?php echo intval(($expandWidth - 75) / 2) - 12 ?>px;
	}

	div.item_edit div.edit_form div.markdown div.preview {
		width: <?php echo intval(($expandWidth - 75) / 2) ?>px;
	}

	div.item_edit div.edit_form input[type="text"], div.item_edit div.edit_form textarea {
		max-width: <?php echo $expandWidth - 75 ?>px;
		width: <?php echo $expandWidth - 75 ?>px;
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