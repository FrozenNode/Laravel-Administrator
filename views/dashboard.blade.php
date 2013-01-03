<div id="dashboard">
	<h2>Dashboard</h2>

	<ul class="models">
		@foreach (Config::get('administrator::administrator.models') as $key => $model)
			@if (Admin\Libraries\ModelHelper::checkPermission($key))
				<?php $key = is_numeric($key) ? $model : $key; ?>
				<li>
					{{ HTML::link(URL::to_route('admin_index', array($key)), $model['title']) }}
				</li>
			@endif
		@endforeach
	</ul>
</div>
