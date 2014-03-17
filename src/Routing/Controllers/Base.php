<?php namespace Frozennode\Administrator\Routing\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

/**
 * The base administrator controller
 */
class Base extends Controller {

	protected $layout = "administrator::layouts.default";

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
			$this->layout->page = false;
			$this->layout->dashboard = false;
		}
	}
}