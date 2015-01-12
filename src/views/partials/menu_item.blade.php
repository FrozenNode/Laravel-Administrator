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
			<a href="{{ url('settings/' . substr($key, strlen($settingsPrefix))) }}">{{$item}}</a>
		@elseif (strpos($key, $pagePrefix) === 0)
			<a href="{{ url('page/' . substr($key, strlen($pagePrefix)) ) }}">{{$item}}</a>
		@else
			<a href="{{ url($key) }}">{{$item}}</a>
		@endif
	</li>
@endif
