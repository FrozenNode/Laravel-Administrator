<header>
	<h1>
		<a href="{{URL::route('admin_dashboard')}}">{{Config::get('administrator::administrator.title')}}</a>
	</h1>

	<ul id="menu">
		@foreach ($menu as $key => $item)
			@if (is_array($item))
				<li class="menu">
					<span>{{$key}}</span>
					<ul>
						@foreach ($item as $k => $subitem)
							<li>
								@if (strpos($k, $settingsPrefix) === false)
									<a href="{{URL::route('admin_index', array($k))}}">{{$subitem}}</a>
								@else
									<a href="{{URL::route('admin_settings', array(substr($k, strlen($settingsPrefix))))}}">{{$subitem}}</a>
								@endif
							</li>
						@endforeach
					</ul>
				</li>
			@else
				<li class="item">
					@if (strpos($key, $settingsPrefix) === false)
						<a href="{{URL::route('admin_index', array($key))}}">{{$item}}</a>
					@else
						<a href="{{URL::route('admin_settings', array(substr($key, strlen($settingsPrefix))))}}">{{$item}}</a>
					@endif
				</li>
			@endif
		@endforeach
	</ul>
	<div id="right_nav">
		@if (count(Config::get('application.languages')) > 0)
			<ul id="lang_menu">
				<li class="menu">
				<span>{{Config::get('application.language')}}</span>
					@if (count(Config::get('application.languages')) > 1)
						<ul>
							@foreach (Config::get('application.languages') as $lang)
								@if (Config::get('application.language') != $lang)
									<li>
										<a href="{{str_replace('/' . Config::get('app.locale') . '/', '/' . $lang . '/', URL::full())}}">{{$lang}}</a>
									</li>
								@endif
							@endforeach
						</ul>
					@endif
				</li>
			</ul>
		@endif
		<a href="{{URL::to('/')}}" id="back_to_site">{{trans('administrator::administrator.backtosite')}}</a>
	</div>
</header>