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
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\File\File as SFile;
use Illuminate\Support\Facades\Validator as LValidator;
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
		$fieldFactory = App::make('admin_field_factory');
		$actionFactory = App::make('admin_action_factory');
		$columnFactory = App::make('admin_column_factory');
		$fields = $fieldFactory->getEditFields();

		//if it's ajax, we just return the item information as json
		if (Request::ajax())
		{
			//try to get the object
			$model = $config->getModel($itemId, $fields, $columnFactory->getIncludedColumns($fields));

			if ($model->exists)
			{
				$model = $config->updateModel($model, $fieldFactory, $actionFactory);
			}

			return $model->toJson();
		}
		else
		{
			$view = View::make("administrator::index", array(
				'itemId' => $itemId,
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
		$fieldFactory = App::make('admin_field_factory');
		$actionFactory = App::make('admin_action_factory');
		$save = $config->save(App::make('request'), $fieldFactory->getEditFields(), $actionFactory->getActionPermissions(), $id);

		if (is_string($save))
		{
			return Response::json(array(
				'success' => false,
				'errors' => $save,
			));
		}
		else
		{
			return Response::json(array(
				'success' => true,
				'data' => $config->getDataModel()->toArray(),
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
		$actionFactory = App::make('admin_action_factory');
		$baseModel = $config->getDataModel();
		$model = $baseModel::find($id);
		$errorResponse = array(
			'success' => false,
			'error' => "There was an error deleting this item. Please reload the page and try again.",
		);

		//if the model or the id don't exist, send back 404
		if (!$model->exists || !$actionFactory->getActionPermissions()['delete'])
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
		$actionFactory = App::make('admin_action_factory');
		$model = $config->getDataModel();
		$model = $model::find($id);
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName);
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
			return Response::json(array('success' => true, 'data' => null));
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
			$configFactory = App::make('admin_config_factory');
			$home = Config::get('administrator::administrator.home_page');

			//first try to find it if it's a model config item
			$config = $configFactory->make($home);

			if ($config->getType() === 'model')
			{
				return Redirect::route('admin_index', array($config->getOption('name')));
			}
			else if ($config->getType() === 'settings')
			{
				return Redirect::route('admin_settings', array($config->getOption('name')));
			}
			else
			{
				throw new \InvalidArgumentException("Administrator: " .  trans('administrator::administrator.valid_home_page'));
			}
		}
	}

	/**
	 * Gets the database results for the current model
	 *
	 * @param string		$modelName
	 *
	 * @return array of rows
	 */
	public function results($modelName)
	{
		$dataTable = App::make('admin_datatable');

		//get the sort options and filters
		$page = Input::get('page', 1);
		$sortOptions = Input::get('sortOptions', array());
		$filters = Input::get('filters', array());

		//return the rows
		return Response::json($dataTable->getRows(App::make('db'), $page, $sortOptions, $filters));
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
		$fieldFactory = App::make('admin_field_factory');

		//get the constraints, the search term, and the currently-selected items
		$constraints = Input::get('constraints', array());
		$term = Input::get('term', '');
		$type = Input::get('type', false);
		$field = Input::get('field', false);
		$selectedItems = Input::get('selectedItems', false);

		//return the rows
		return Response::json($fieldFactory->updateRelationshipOptions($field, $type, $constraints, $selectedItems, $term));
	}

	/**
	 * The GET method that displays a file field's file
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
		$fieldFactory = App::make('admin_field_factory');

		//get the model and the field object
		$field = $fieldFactory->findField($fieldName);

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
		$dataTable = App::make('admin_datatable');

		//get the inputted rows and the model rows
		$rows = (int) Input::get('rows', 20);
		$dataTable->setRowsPerPage(App::make('session'), 0, $rows);

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
		$config = App::make('itemconfig');
		$save = $config->save(App::make('request'), App::make('admin_field_factory')->getEditFields());

		if (is_string($save))
		{
			return Response::json(array(
				'success' => false,
				'errors' => $save,
			));
		}
		else
		{
			return Response::json(array(
				'success' => true,
				'data' => $config->getDataModel(),
			));
		}
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
		$actionFactory = App::make('admin_action_factory');
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName);
		$result = $action->perform($config->getDataModel());

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

	/**
	 * POST method for switching a user's locale
	 *
	 * @param string	$locale
	 *
	 * @return JSON
	 */
	public function switchLocale($locale)
	{
		if (in_array($locale, Config::get('administrator::administrator.locales')))
		{
			Session::put('administrator_locale', $locale);
		}

		return Redirect::back();
	}

}