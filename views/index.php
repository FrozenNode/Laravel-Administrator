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
			filters: <?php echo json_encode($filters); ?>,
			edit_fields: <?php echo json_encode($editFields); ?>,
			data_model: <?php echo json_encode($dataModel); ?>,
			column_model: <?php echo json_encode($columns); ?>
		};
</script>

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