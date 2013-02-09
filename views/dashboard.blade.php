<div id="dashboard">
	<h2>{{ __('administrator::administrator.dashboard') }}</h2>

	<ul class="models">
		@foreach ($menu as $key => $item)
			<li>
				@if (is_array($item))
					<span>{{$key}}</span>
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
</div>
