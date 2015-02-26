<?php namespace Frozennode\Administrator\Http\Middleware;

use App;
use Closure;

class ValidateSettings {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$settingsName = $request->route()->parameter('settings');

		App::singleton('itemconfig', function($app) use ($settingsName)
		{
			$configFactory = App::make('admin_config_factory');
			return $configFactory->make($configFactory->getSettingsPrefix() . $settingsName, true);
		});

		return $next($request);
	}

}