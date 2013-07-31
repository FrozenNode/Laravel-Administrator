<!DOCTYPE html>
<html lang="<?php echo Config::get('application.language') ?>">
<head>
	<meta charset="utf-8">
	<title>{{ Config::get('administrator::administrator.title') }}</title>

	<link href="{{asset('packages/frozennode/administrator/css/ui/jquery-ui-1.9.1.custom.min.css')}}" media="all" type="text/css" rel="stylesheet">
	<link href="{{asset('packages/frozennode/administrator/css/ui/jquery.ui.timepicker.css')}}" media="all" type="text/css" rel="stylesheet">
	<link href="{{asset('packages/frozennode/administrator/js/jquery/select2/select2.css')}}" media="all" type="text/css" rel="stylesheet">
	<link href="{{asset('packages/frozennode/administrator/css/jquery.lw-colorpicker.css')}}" media="all" type="text/css" rel="stylesheet">
	<link href="{{asset('packages/frozennode/administrator/css/main.css')}}" media="all" type="text/css" rel="stylesheet">

</head>
<body>
	<div class="row-fluid">
		<div id="wrapper">
			@include('administrator::partials.header')

			{{ $content }}

			@include('administrator::partials.footer')
		</div>
	</div>

	<script src="{{asset('packages/frozennode/administrator/js/jquery/jquery-1.8.2.min.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/jquery/select2/select2.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/jquery/jquery-ui-1.10.3.custom.min.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/jquery/jquery-ui-timepicker-addon.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/ckeditor/ckeditor.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/ckeditor/adapters/jquery.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/markdown.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/plupload/js/plupload.full.js')}}"></script>

	@if (Config::get('app.locale') != 'en')
		<script src="{{asset('packages/frozennode/administrator/js/plupload/js/i18n/'.Config::get('app.locale').'.js')}}"></script>
		<script src="{{asset('packages/frozennode/administrator/js/jquery/localization/jquery-ui-timepicker-'.Config::get('app.locale').'.js')}}"></script>
		<script src="{{asset('packages/frozennode/administrator/js/jquery/i18n/jquery.ui.datepicker-'.Config::get('app.locale').'.js')}}"></script>
	@endif

	<script src="{{asset('packages/frozennode/administrator/js/knockout/knockout-2.2.0.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/knockout/knockout.mapping.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/knockout/KnockoutNotification.knockout.min.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/knockout/knockout.updateData.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/knockout/custom-bindings.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/accounting.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/jquery/jquery.lw-colorpicker.min.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/history/native.history.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/page.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/admin.js')}}"></script>
	<script src="{{asset('packages/frozennode/administrator/js/settings.js')}}"></script>

</body>
</html>