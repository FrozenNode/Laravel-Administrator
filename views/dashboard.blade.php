<div id="dashboard">
	<h2>Dashboard</h2>

	<ul class="models">
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
</div>
