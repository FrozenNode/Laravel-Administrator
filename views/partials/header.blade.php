<header>
	<h1>
		<a href="{{URL::to_route('admin_dashboard')}}">{{Config::get('administrator::administrator.title')}}</a>
	</h1>

	<ul id="menu">
		@foreach ($menu as $key => $item)
			@if (is_array($item))
				<li class="menu">
					<span>{{$key}}</span>
					<ul>
						@foreach ($item as $k => $subitem)
							<li>
								<a href="{{URL::to_route('admin_index', array($k))}}">{{$subitem}}</a>
							</li>
						@endforeach
					</ul>
				</li>
			@else
				<li class="item">
					<a href="{{URL::to_route('admin_index', array($key))}}">{{$item}}</a>
				</li>
			@endif
		@endforeach
	</ul>
	<a href="{{URL::base()}}" id="back_to_site">{{__('administrator::administrator.backtosite')}}</a>
</header>
