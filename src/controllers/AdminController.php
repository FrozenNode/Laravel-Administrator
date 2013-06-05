<?php namespace Frozennode\Administrator;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Routing\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\File as SFile;
use Illuminate\Support\Facades\Validator;
use Frozennode\Administrator\Fields\Field;

/**
 * Handles all requests related to managing the data models
 */
class AdminController extends Controller
{

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
		}
	}

	/**
	 * The main view for any of the data models
	 *
	 * @return Response
	 */
	public function index()
	{
		//set the layout content and title
		$this->layout->content = View::make("administrator::index");
	}


	/**
	 * Gets the item edit page / information
	 *
	 * @param string		$modelName
	 * @param mixed			$itemId
	 */
	public function item($modelName, $itemId = false)
	{
		$config = App::make('itemconfig');

		//try to get the object
		$model = ModelHelper::getModel($itemId, true);

		//if it's ajax, we just return the item information as json
		//otherwise we load up the index page and dump values into
		if (Request::ajax())
		{
			return $model->toJson();
		}
		else
		{
			//if the $itemId is false, we can assume this is a request for /new
			//if the user doesn't have the proper permissions to create, redirect them back to the model page
			if (!$itemId && !$config->actionPermissions['create'])
			{
				return Redirect::route('admin_index', array($config->name));
			}

			$view = View::make("administrator::index", array(
				'model' => $model,
			));

			//set the layout content and title
			$this->layout->content = $view;
		}
	}

	/**
	 * POST save method that accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
	 *
	 * @param string		$modelName
	 * @param int			$id
	 *
	 * @return JSON
	 */
	public function save($modelName, $id = false)
	{
		$config = App::make('itemconfig');
		$model = ModelHelper::getModel($id, false, false, true);

		//fill the model with our input
		ModelHelper::fillModel($model);

		$rules = isset($model::$rules) ? $model::$rules : array();

		//if the model exists, this is an update
		if ($model->exists)
		{
			//check if the user has permission to update
			if (!$config->actionPermissions['update'])
			{
				return Response::json(array(
					'success' => false,
					'errors' => 'There was an error updating this item. Please reload the page and try again.',
				));
			}

			//so only include dirty fields
			$data = $model->getDirty();

			//and validate the fields that are being updated
			$rules = array_intersect_key($rules, $data);
		}
		else
		{
			//check if the user has permission to create
			if (!$config->actionPermissions['create'])
			{
				return Response::json(array(
					'success' => false,
					'errors' => 'There was an error creating this item. Please reload the page and try again.',
				));
			}

			//otherwise validate everything
			$data = $model->getAttributes();
		}

		//validate the model
		$validator = Validator::make($data, $rules);

		if ($validator->fails())
		{
			return Response::json(array(
				'success' => false,
				'errors' => $validator->errors()->all(),
			));
		}
		else
		{
			$model->save();

			//Save the relationships
			ModelHelper::saveRelationships($model);

			return Response::json(array(
				'success' => true,
				'data' => $model->toArray(),
			));
		}
	}

	/**
	 * POST delete method that accepts data via JSON POST and either saves an old
	 *
	 * @param string		$modelName
	 * @param int			$id
	 *
	 * @return JSON
	 */
	public function delete($modelName, $id)
	{
		$config = App::make('itemconfig');
		$model = ModelHelper::getModel($id);
		$errorResponse = array(
			'success' => false,
			'error' => "There was an error deleting this item. Please reload the page and try again.",
		);

		//if the model or the id don't exist, send back 404
		if (!$model->exists || !$config->actionPermissions['delete'])
		{
			return Response::json($errorResponse);
		}

		//delete the model
		if ($model->delete())
		{
			return Response::json(array(
				'success' => true,
			));
		}
		else
		{
			return Response::json($errorResponse);
		}
	}

	/**
	 * POST method for handling custom actions
	 *
	 * @param string		$modelName
	 * @param int			$id
	 *
	 * @return JSON
	 */
	public function customAction($modelName, $id = null)
	{
		$config = App::make('itemconfig');
		$isSettings = is_a($config, 'Admin\\Libraries\\SettingsConfig');
		$data = $isSettings ? $config->data : ModelHelper::getModel($id, false, true);
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = Action::getByName($actionName);
		$result = $action->perform($data);

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return Response::json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			return Response::json(array('success' => false, 'error' => $action->messages['error']));
		}
		else
		{
			//if this is a settings config, we want to save the data before returning
			if ($isSettings)
			{
				$config->putToJSON($data);
			}

			return Response::json(array('success' => true, 'data' => $isSettings ? $data : null));
		}
	}

	/**
	 * Shows the dashboard page
	 *
	 * @return Response
	 */
	public function dashboard()
	{
		//if the dev has chosen to use a dashboard
		if (Config::get('administrator::administrator.use_dashboard'))
		{
			//set the layout content
			$this->layout->content = View::make(Config::get('administrator::administrator.dashboard_view'));
		}
		//else we should redirect to the menu item
		else
		{
			$home = Config::get('administrator::administrator.home_page');

			//first try to find it if it's a model config item
			if ($config = ModelConfig::get($home))
			{
				return Redirect::route('admin_index', array($config->name));
			}
			else if ($config = SettingsConfig::get($home))
			{
				return Redirect::route('admin_settings', array($config->name));
			}
			else
			{
				throw new Exception("Administrator: " .  __('administrator::administrator.valid_home_page'));
			}
		}
	}

	/**
	 * Gets the item edit page / information
	 *
	 * @param string		$modelName
	 *
	 * @return array of rows
	 */
	public function results($modelName)
	{
		$config = App::make('itemconfig');

		//get the sort options and filters
		$sortOptions = Input::get('sortOptions', array());
		$filters = Input::get('filters', array());

		//return the rows
		return Response::json(ModelHelper::getRows($sortOptions, $filters));
	}

	/**
	 * Gets a list of related items given constraints
	 *
	 * @param string	$modelName
	 *
	 * @return array of objects [{id: string} ... {1: 'name'}, ...]
	 */
	public function updateOptions($modelName)
	{
		$config = App::make('itemconfig');

		//get the constraints, the search term, and the currently-selected items
		$constraints = Input::get('constraints', array());
		$term = Input::get('term', '');
		$type = Input::get('type', false);
		$field = Input::get('field', false);
		$selectedItems = Input::get('selectedItems', false);

		//return the rows
		return Response::json(ModelHelper::updateRelationshipOptions($field, $type, $constraints, $selectedItems, $term));
	}

	/**
	 * The GET method that displays an file field's file
	 *
	 * @return Image / File
	 */
	public function displayFile()
	{
		//get the stored path of the original
		$path = Input::get('path');
		$data = File::get($path);
		$file = new SFile($path);

		$headers = array(
			'Content-Type' => $file->getMimeType(),
			'Content-Length' => $file->getSize(),
			'Content-Disposition' => 'attachment; filename="' . $file->getFilename() . '"'
		);

		return Response::make($data, 200, $headers);
	}

	/**
	 * The POST method that runs when a user uploads a file on a file field
	 *
	 * @param string	$modelName
	 * @param string	$fieldName
	 *
	 * @return JSON
	 */
	public function fileUpload($modelName, $fieldName)
	{
		$config = App::make('itemconfig');

		//get the model and the field object
		$field = Field::findField($config, $fieldName);

		return Response::JSON($field->doUpload());
	}

	/**
	 * The POST method for setting a user's rows per page
	 *
	 * @param string	$modelName
	 *
	 * @return JSON
	 */
	public function rowsPerPage($modelName)
	{
		$config = App::make('itemconfig');

		//get the inputted rows and the model rows
		$rows = (int) Input::get('rows', 20);
		$config->setRowsPerPage($rows);

		return Response::JSON(array('success' => true));
	}

	/**
	 * The main view for any of the settings pages
	 *
	 * @param string	$settingsName
	 *
	 * @return Response
	 */
	public function settings($settingsName)
	{
		$config = App::make('itemconfig');

		//set the layout content and title
		$this->layout->content = View::make("administrator::settings");
	}

	/**
	 * POST save settings method that accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
	 *
	 * @return JSON
	 */
	public function settingsSave()
	{
		return App::make('itemconfig')->save();
	}

	/**
	 * POST method for handling custom actions on the settings page
	 *
	 * @param string	$settingsName
	 *
	 * @return JSON
	 */
	public function settingsCustomAction($settingsName)
	{
		$config = App::make('itemconfig');
		$model = ModelHelper::getModel($id, false, true);
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = Action::getByName($actionName);
		$result = $action->perform($model);

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return Response::json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			return Response::json(array('success' => false, 'error' => $action->messages['error']));
		}
		else
		{
			return Response::json(array('success' => true));
		}
	}

}