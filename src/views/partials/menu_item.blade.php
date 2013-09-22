@if (is_array($item))
	<li class="menu">
		<span>{{$key}}</span>
		<ul>
			@foreach ($item as $k => $subitem)
				<?php echo View::make("administrator::partials.menu_item", array(
					'item' => $subitem,
					'key' => $k,
					'settingsPrefix' => $settingsPrefix,
					'pagePrefix' => $pagePrefix
				))?>
			@endforeach
		</ul>
	</li>
@else
	<li class="item">
		@if (strpos($key, $settingsPrefix) === 0)
			<a href="{{URL::route('admin_settings', array(substr($key, strlen($settingsPrefix))))}}">{{$item}}</a>
		@elseif (strpos($key, $pagePrefix) === 0)
			<a href="{{URL::route('admin_page', array(substr($key, strlen($pagePrefix))))}}">{{$item}}</a>
		@else
			<a href="{{URL::route('admin_index', array($key))}}">{{$item}}</a>
		@endif
	</li>
@endif
