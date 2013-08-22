<!DOCTYPE html>
<html lang="<?php echo Config::get('application.language') ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
	<title>{{ Config::get('administrator::administrator.title') }}</title>

	@foreach ($css as $url)
		<link href="{{$url}}" media="all" type="text/css" rel="stylesheet">
	@endforeach

</head>
<body>
	<div id="wrapper">
		@include('administrator::partials.header')

		{{ $content }}

		@include('administrator::partials.footer')
	</div>

	@foreach ($js as $url)
		<script src="{{$url}}"></script>
	@endforeach
</body>
</html>