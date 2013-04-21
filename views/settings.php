<div id="settings_page">
	<div id="content" data-bind="template: 'settingsTemplate'"></div>
</div>

<script type="text/javascript">
	var site_url = "<?php echo URL::to('/') ?>",
		base_url = "<?php echo $baseUrl ?>/",
		asset_url = "<?php echo $assetUrl ?>",
		save_url = "<?php echo URL::to_route('admin_settings_save', array($config->name)) ?>",
		custom_action_url = "<?php echo URL::to_route('admin_settings_custom_action', array($config->name)) ?>",
		file_url = "<?php echo URL::to_route('admin_settings_display_file', array($config->name)) ?>",
		route = "<?php echo $route ?>",
		csrf = "<?php echo Session::token() ?>",
		language = "<?php echo Config::get('application.language') ?>",
		adminData = {
			name: "<?php echo $config->name ?>",
			title: "<?php echo $config->title ?>",
			data: <?php echo json_encode($config->data) ?>,
			actions: <?php echo json_encode($config->actions) ?>,
			edit_fields: <?php echo json_encode($editFields['arrayFields']) ?>,
			languages: <?php echo json_encode(Lang::file('administrator', Config::get('application.language'), 'knockout')) ?>
		};
</script>

<?php echo Form::token() ?>

<script id="settingsTemplate" type="text/html">
	<?php echo View::make("administrator::templates.settings")?>
</script>