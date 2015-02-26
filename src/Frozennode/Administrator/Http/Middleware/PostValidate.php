<?php namespace Frozennode\Administrator\Http\Middleware;

use App;
use Closure;

class PostValidate {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$config = App::make('itemconfig');

		//if the model doesn't exist at all, redirect to 404
		if (!$config)
		{
			App::abort(404, 'Page not found');
		}

		//check the permission
		$p = $config->getOption('permission');

		//if the user is simply not allowed permission to this model, redirect them to the dashboard
		if (!$p)
		{
			return redirect()->route('admin_dashboard');
		}

		//get the settings data if it's a settings page
		if ($config->getType() === 'settings')
		{
			$config->fetchData(App::make('admin_field_factory')->getEditFields());
		}

		//otherwise if this is a response, return that
		if (is_a($p, 'Illuminate\Http\JsonResponse') || is_a($p, 'Illuminate\Http\Response') || is_a($p, 'Illuminate\\Http\\RedirectResponse'))
		{
			return $p;
		}

		return $next($request);
	}

}