<header>
	<h1>
		{{ HTML::link(URL::to_route('admin_dashboard'), Config::get('administrator::administrator.title')) }}
	</h1>

	<ul id="menu">
		@foreach ($menu as $key => $item)
			@if (is_array($item))
				<li class="menu">
					<span>{{$key}}</span>
					<ul>
						@foreach ($item as $k => $subitem)
							<li>
								{{ HTML::link(URL::to_route('admin_index', array($k)), $subitem) }}
							</li>
						@endforeach
					</ul>
				</li>
			@else
				<li class="item">
					{{ HTML::link(URL::to_route('admin_index', array($key)), $item) }}
				</li>
			@endif
		@endforeach
	</ul>
	<p id="utility_nav">
		{{ HTML::link(URL::base(), "Back to Site") }}
	</p>
</header>