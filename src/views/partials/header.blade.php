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
		@if (count(Config::get('administrator::administrator.locales')) > 0)
			<ul id="lang_menu">
				<li class="menu">
				<span>{{Config::get('app.locale')}}</span>
					@if (count(Config::get('administrator::administrator.locales')) > 1)
						<ul>
							@foreach (Config::get('administrator::administrator.locales') as $lang)
								@if (Config::get('app.locale') != $lang)
									<li>
										<a href="{{URL::route('admin_switch_locale', array($lang))}}">{{$lang}}</a>
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