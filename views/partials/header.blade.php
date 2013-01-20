<header>
	<h1>
		{{ HTML::link(URL::to_route('admin_dashboard'), Config::get('administrator::administrator.title')) }}
	</h1>

	<ul id="tabs">
		@foreach ($menu as $key => $item)
			<li>
				@if (is_array($item))
					<ul>
						@foreach ($item as $k => $subitem)
							<li>
								{{ HTML::link(URL::to_route('admin_index', array($k)), $subitem) }}
							</li>
						@endforeach
					</ul>
				@else
					{{ HTML::link(URL::to_route('admin_index', array($key)), $item) }}
				@endif
			</li>
		@endforeach
	</ul>
	<p id="utility_nav">
		{{ HTML::link(URL::base(), "Back to Site") }}
	</p>
</header>