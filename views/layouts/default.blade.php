<!DOCTYPE html>
<html lang="<?php echo Config::get('application.language') ?>">
<head>
	<meta charset="utf-8">
	<title>{{ Config::get('administrator::administrator.title') }}</title>

	{{ Asset::container('container')->styles() }}
</head>
<body>
	<div class="row-fluid">
		<div id="wrapper">

			@include('administrator::partials.header')

			{{ $content }}

			@include('administrator::partials.footer')
		</div>
	</div>

	{{ Asset::container('container')->scripts() }}
</body>
</html>