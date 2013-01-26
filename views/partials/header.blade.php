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

		@if (count(Config::get('application.languages')))
			<li class="menu">
			<span>{{Config::get('application.language')}}</span>
				<ul>
					@foreach (Config::get('application.languages') as $key => $lang)
						@if (Config::get('application.language') != $lang)
							<li>
								<a href="{{URL::base() . '/' . $lang . '/admin' }}">{{$lang}}</a>
							</li>
						@endif
					@endforeach
				</ul>
			</li>
		@endif
	</ul>
	<a href="{{URL::base()}}" id="back_to_site">Back to Site</a>
</header>
