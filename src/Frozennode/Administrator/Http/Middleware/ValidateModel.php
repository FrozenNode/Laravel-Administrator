<?php namespace Frozennode\Administrator\Http\Middleware;

use Closure;
use App;

class ValidateModel {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        $modelName = app('administrator.4.1') ? $request->route()->parameter('model') : $request->route()->getParameter('model');

        App::singleton('itemconfig', function($app) use ($modelName)
        {
            $configFactory = app('admin_config_factory');

            return $configFactory->make($modelName, true);
        });

		return $next($request);
	}

}
