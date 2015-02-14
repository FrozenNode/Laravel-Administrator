<?php namespace Frozennode\Administrator\Http\Middleware;

use Closure;
use App;

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
        $settingsName = App::make('administrator.4.1') ? $request->route()->parameter('settings') : $request->route()->getParameter('settings');

        App::singleton('itemconfig', function($app) use ($settingsName)
        {
            $configFactory = App::make('admin_config_factory');
            return $configFactory->make($configFactory->getSettingsPrefix() . $settingsName, true);
        });

		return $next($request);
	}

}
