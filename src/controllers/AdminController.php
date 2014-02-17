<?php namespace Frozennode\Administrator;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use AdministratorBaseController as Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
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
			$this->layout->page = false;
			$this->layout->dashboard = false;
		}
	}

	/**
	 * The main view for any of the data models
	 *
	 * @return Response
	 */
	public function index($modelName)
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
	public function item($modelName, $itemId = 0)
	{
		$config = App::make('itemconfig');
		$fieldFactory = App::make('admin_field_factory');
		$actionFactory = App::make('admin_action_factory');
		$columnFactory = App::make('admin_column_factory');
		$actionPermissions = $actionFactory->getActionPermissions();
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

			$response = $actionPermissions['view'] ? Response::json($model) : Response::json(array(
				'success' => false,
				'errors' => "You do not have permission to view this item",
			));

			//set the Vary : Accept header to avoid the browser caching the json response
			return $response->header('Vary', 'Accept');
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
			//override the config options so that we can get the latest
			App::make('admin_config_factory')->updateConfigOptions();

			//grab the latest model data
			$columnFactory = App::make('admin_column_factory');
			$fields = $fieldFactory->getEditFields();
			$model = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));

			if ($model->exists)
			{
				$model = $config->updateModel($model, $fieldFactory, $actionFactory);
			}

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
		$actionFactory = App::make('admin_action_factory');
		$baseModel = $config->getDataModel();
		$model = $baseModel::find($id);
		$errorResponse = array(
			'success' => false,
			'error' => "There was an error deleting this item. Please reload the page and try again.",
		);

		//if the model or the id don't exist, send back an error
		$permissions = $actionFactory->getActionPermissions();

		if (!$model->exists || !$permissions['delete'])
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
	 * POST method for handling custom model actions
	 *
	 * @param string		$modelName
	 *
	 * @return JSON
	 */
	public function customModelAction($modelName)
	{
		$config = App::make('itemconfig');
		$actionFactory = App::make('admin_action_factory');
		$actionName = Input::get('action_name', false);
		$dataTable = App::make('admin_datatable');

		//get the sort options and filters
		$page = Input::get('page', 1);
		$sortOptions = Input::get('sortOptions', array());
		$filters = Input::get('filters', array());

		//get the prepared query options
		$prepared = $dataTable->prepareQuery(App::make('db'), $page, $sortOptions, $filters);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName, true);
		$result = $action->perform($prepared['query']);

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return Response::json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');

			return Response::json(array('success' => false, 'error' => $messages['error']));
		}
		else
		{
			$response = array('success' => true);

			//if it's a download response, flash the response to the session and return the download link
			if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse'))
			{
				$file = $result->getFile()->getRealPath();
				$headers = $result->headers->all();
				Session::put('administrator_download_response', array('file' => $file, 'headers' => $headers));

				$response['download'] = URL::route('admin_file_download');
			}
			//if it's a redirect, put the url into the redirect key so that javascript can transfer the user
			else if (is_a($result, '\Illuminate\Http\RedirectResponse'))
			{
				$response['redirect'] = $result->getTargetUrl();
			}

			return Response::json($response);
		}
	}

	/**
	 * POST method for handling custom model item actions
	 *
	 * @param string		$modelName
	 * @param int			$id
	 *
	 * @return JSON
	 */
	public function customModelItemAction($modelName, $id = null)
	{
		$config = App::make('itemconfig');
		$actionFactory = App::make('admin_action_factory');
		$model = $config->getDataModel();
		$model = $model::find($id);
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName);
		$result = $action->perform($model);

		//override the config options so that we can get the latest
		App::make('admin_config_factory')->updateConfigOptions();

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return Response::json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');
			return Response::json(array('success' => false, 'error' => $messages['error']));
		}
		else
		{
			$fieldFactory = App::make('admin_field_factory');
			$columnFactory = App::make('admin_column_factory');
			$fields = $fieldFactory->getEditFields();
			$model = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));

			if ($model->exists)
			{
				$model = $config->updateModel($model, $fieldFactory, $actionFactory);
			}

			$response = array('success' => true, 'data' => $model->toArray());

			//if it's a download response, flash the response to the session and return the download link
			if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse'))
			{
				$file = $result->getFile()->getRealPath();
				$headers = $result->headers->all();
				Session::put('administrator_download_response', array('file' => $file, 'headers' => $headers));

				$response['download'] = URL::route('admin_file_download');
			}
			//if it's a redirect, put the url into the redirect key so that javascript can transfer the user
			else if (is_a($result, '\Illuminate\Http\RedirectResponse'))
			{
				$response['redirect'] = $result->getTargetUrl();
			}

			return Response::json($response);
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
			//set the layout dashboard
			$this->layout->dashboard = true;

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

			if (!$config)
			{
				throw new \InvalidArgumentException("Administrator: " .  trans('administrator::administrator.valid_home_page'));
			}
			else if ($config->getType() === 'model')
			{
				return Redirect::route('admin_index', array($config->getOption('name')));
			}
			else if ($config->getType() === 'settings')
			{
				return Redirect::route('admin_settings', array($config->getOption('name')));
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
		return Response::json($dataTable->getRows(App::make('db'), $filters, $page, $sortOptions));
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
		$response = array();

		//iterate over the supplied constrained fields
		foreach (Input::get('fields', array()) as $field)
		{
			//get the constraints, the search term, and the currently-selected items
			$constraints = array_get($field, 'constraints', array());
			$term = array_get($field, 'term', array());
			$type = array_get($field, 'type', false);
			$fieldName = array_get($field, 'field', false);
			$selectedItems = array_get($field, 'selectedItems', false);

			$response[$fieldName] = $fieldFactory->updateRelationshipOptions($fieldName, $type, $constraints, $selectedItems, $term);
		}

		return Response::json($response);
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
	 * The GET method that runs when a user needs to download a file
	 *
	 * @return JSON
	 */
	public function fileDownload()
	{
		if ($response = Session::get('administrator_download_response'))
		{
			Session::forget('administrator_download_response');
			$filename = substr($response['headers']['content-disposition'][0], 22, -1);

			return Response::download($response['file'], $filename, $response['headers']);
		}
		else
		{
			return Redirect::back();
		}
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
		$dataTable->setRowsPerPage(App::make('session.store'), 0, $rows);

		return Response::JSON(array('success' => true));
	}

	/**
	 * The pages view
	 *
	 * @return Response
	 */
	public function page($page)
	{
		//set the page
		$this->layout->page = $page;

		//set the layout content and title
		$this->layout->content = View::make($page);
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
			//override the config options so that we can get the latest
			App::make('admin_config_factory')->updateConfigOptions();

			return Response::json(array(
				'success' => true,
				'data' => $config->getDataModel(),
				'actions' => App::make('admin_action_factory')->getActionsOptions(),
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
		$data = $config->getDataModel();
		$result = $action->perform($data);

		//override the config options so that we can get the latest
		App::make('admin_config_factory')->updateConfigOptions();

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return Response::json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');

			return Response::json(array('success' => false, 'error' => $messages['error']));
		}
		else
		{
			$response = array('success' => true, 'actions' => $actionFactory->getActionsOptions(true));

			//if it's a download response, flash the response to the session and return the download link
			if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse'))
			{
				$file = $result->getFile()->getRealPath();
				$headers = $result->headers->all();
				Session::put('administrator_download_response', array('file' => $file, 'headers' => $headers));

				$response['download'] = URL::route('admin_file_download');
			}
			//if it's a redirect, put the url into the redirect key so that javascript can transfer the user
			else if (is_a($result, '\Illuminate\Http\RedirectResponse'))
			{
				$response['redirect'] = $result->getTargetUrl();
			}

			return Response::json($response);
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
