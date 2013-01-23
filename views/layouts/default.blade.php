<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ Config::get('administrator::administrator.title') }}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">

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