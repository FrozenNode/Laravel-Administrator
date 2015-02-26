<?php namespace Frozennode\Administrator\Http\Middleware;

use Closure;

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
		$modelName = $request->route()->parameter('model');

		app()->singleton('itemconfig', function($app) use ($modelName)
		{
			$configFactory = app('admin_config_factory');

			return $configFactory->make($modelName, true);
		});

		return $next($request);
	}

}