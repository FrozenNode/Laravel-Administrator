<header>
	<h1>
		{{ HTML::link(URL::to_route('admin_dashboard'), Config::get('administrator::administrator.title')) }}
	</h1>

	<ul id="tabs">
		@foreach (Config::get('administrator::administrator.models') as $key => $model)
			<?php $key = is_numeric($key) ? $model : $key; ?>
			<li class="@if ($modelName == $key) current @endif">
				{{ HTML::link(URL::to_route('admin_index', array($key)), $model['title']) }}
			</li>
		@endforeach
	</ul>
	<p id="utility_nav">
		{{ HTML::link(URL::base(), "Back to Site") }}
	</p>
</header>