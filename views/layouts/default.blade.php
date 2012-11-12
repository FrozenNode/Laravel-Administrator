<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>{{ Config::get('administrator::administrator.title') }}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">

	<!--styles -->
	{{ Asset::container('container')->styles() }}
	
	<!-- Soporte para IE6-8 de elementos HTML5 -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<!-- favicon and touch icons -->
	<link rel="shortcut icon" href="public/img/ico/favicon.ico">
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="public/img/ico/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="public/img/ico/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="public/img/ico/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="public/img/ico/apple-touch-icon-57-precomposed.png">
</head>
<body>
	<div class="row-fluid">
		<div id="wrapper">
			
			@include('administrator::partials.header')
			
			{{ $content }}
			
			@include('administrator::partials.footer')
		</div>
	</div>

	<!-- JavaScript placed at the end of the document so the pages load faster -->
	{{ Asset::container('container')->scripts() }}
</body>
</html>