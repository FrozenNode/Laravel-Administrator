<div id="settings_page">
	<div id="content" data-bind="template: 'settingsTemplate'"></div>
</div>

<script type="text/javascript">
	var site_url = "<?php echo url('/') ?>",
		base_url = "<?php echo $baseUrl ?>/",
		asset_url = "<?php echo $assetUrl ?>",
		save_url = "<?php echo admin_url('settings/'. $config->getOption('name') .'/save') ?>",
		custom_action_url = "<?php echo admin_url('settings/'. $config->getOption('name') .'/custom_action') ?>",
		file_url = "<?php echo admin_url('settings/' . $config->getOption('name') . '/file')  ?>",
		route = "<?php echo $route ?>",
		csrf = "<?php echo Session::token() ?>",
		language = "<?php echo Config::get('app.locale') ?>",
		adminData = {
			name: "<?php echo $config->getOption('name') ?>",
			title: "<?php echo $config->getOption('title') ?>",
			data: <?php echo json_encode($config->getDataModel()) ?>,
			actions: <?php echo json_encode($actions) ?>,
			edit_fields: <?php echo json_encode($arrayFields) ?>,
			languages: <?php echo json_encode(trans('administrator::knockout')) ?>
		};
</script>

<?php echo Form::token() ?>

<script id="settingsTemplate" type="text/html">
	<?php echo View::make("administrator::templates.settings")?>
</script>