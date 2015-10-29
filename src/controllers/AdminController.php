<?php namespace Frozennode\Administrator;

use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Session\SessionManager as Session;
use Symfony\Component\HttpFoundation\File\File as SFile;

/**
 * Handles all requests related to managing the data models
 */
class AdminController extends Controller {

	/**
	 * @var \Illuminate\Http\Request
	 */
	protected $request;

	/**
	 * @var \Illuminate\Session\SessionManager
	 */
	protected $session;
	
	/**
	 * @var string
	 */
	protected $formRequestErrors;

	/**
	 * @var string
	 */
	protected $layout = "administrator::layouts.default";

	/**
	 * @param \Illuminate\Http\Request              $request
	 * @param \Illuminate\Session\SessionManager    $session
	 */
	public function __construct(Request $request, Session $session)
	{
		$this->request = $request;
		$this->session = $session;
		
		$this->formRequestErrors = $this->resolveDynamicFormRequestErrors($request);

		if ( ! is_null($this->layout))
		{
			$this->layout = view($this->layout);

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
		$this->layout->content = view("administrator::index");

		return $this->layout;
	}

	/**
	 * Gets the item edit page / information
	 *
	 * @param string		$modelName
	 * @param mixed			$itemId
	 */
	public function item($modelName, $itemId = 0)
	{
		$config = app('itemconfig');
		$fieldFactory = app('admin_field_factory');
		$actionFactory = app('admin_action_factory');
		$columnFactory = app('admin_column_factory');
		$actionPermissions = $actionFactory->getActionPermissions();
		$fields = $fieldFactory->getEditFields();

		//if it's ajax, we just return the item information as json
		if ($this->request->ajax())
		{
			//try to get the object
			$model = $config->getModel($itemId, $fields, $columnFactory->getIncludedColumns($fields));

			if ($model->exists)
			{
				$model = $config->updateModel($model, $fieldFactory, $actionFactory);
			}

			$response = $actionPermissions['view'] ? response()->json($model) : response()->json(array(
				'success' => false,
				'errors' => "You do not have permission to view this item",
			));

			//set the Vary : Accept header to avoid the browser caching the json response
			return $response->header('Vary', 'Accept');
		}
		else
		{
			$view = view("administrator::index", array(
				'itemId' => $itemId,
			));

			//set the layout content and title
			$this->layout->content = $view;

			return $this->layout;
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
		$config = app('itemconfig');
		$fieldFactory = app('admin_field_factory');
		$actionFactory = app('admin_action_factory');
		
		if (array_key_exists('form_request', $config->getOptions()) && $this->formRequestErrors !== null) {
			return response()->json(array(
				'success' => false,
				'errors'  => $this->formRequestErrors,
			));
		}
		
		$save = $config->save($this->request, $fieldFactory->getEditFields(), $actionFactory->getActionPermissions(), $id);

		if (is_string($save))
		{
			return response()->json(array(
				'success' => false,
				'errors' => $save,
			));
		}
		else
		{
			//override the config options so that we can get the latest
			app('admin_config_factory')->updateConfigOptions();

			//grab the latest model data
			$columnFactory = app('admin_column_factory');
			$fields = $fieldFactory->getEditFields();
			$model = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));

			if ($model->exists)
			{
				$model = $config->updateModel($model, $fieldFactory, $actionFactory);
			}

			return response()->json(array(
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
		$config = app('itemconfig');
		$actionFactory = app('admin_action_factory');
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
			return response()->json($errorResponse);
		}

		//delete the model
		if ($model->delete())
		{
			return response()->json(array(
				'success' => true,
			));
		}
		else
		{
			return response()->json($errorResponse);
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
		$config = app('itemconfig');
		$actionFactory = app('admin_action_factory');
		$actionName = $this->request->input('action_name', false);
		$dataTable = app('admin_datatable');

		//get the sort options and filters
		$page = $this->request->input('page', 1);
		$sortOptions = $this->request->input('sortOptions', array());
		$filters = $this->request->input('filters', array());

		//get the prepared query options
		$prepared = $dataTable->prepareQuery(app('db'), $page, $sortOptions, $filters);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName, true);
		$result = $action->perform($prepared['query']);

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return response()->json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');

			return response()->json(array('success' => false, 'error' => $messages['error']));
		}
		else
		{
			$response = array('success' => true);

			//if it's a download response, flash the response to the session and return the download link
			if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse'))
			{
				$file = $result->getFile()->getRealPath();
				$headers = $result->headers->all();
				$this->session->put('administrator_download_response', array('file' => $file, 'headers' => $headers));

				$response['download'] = route('admin_file_download');
			}
			//if it's a redirect, put the url into the redirect key so that javascript can transfer the user
			else if (is_a($result, '\Illuminate\Http\RedirectResponse'))
			{
				$response['redirect'] = $result->getTargetUrl();
			}

			return response()->json($response);
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
		$config = app('itemconfig');
		$actionFactory = app('admin_action_factory');
		$model = $config->getDataModel();
		$model = $model::find($id);
		$actionName = $this->request->input('action_name', false);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName);
		$result = $action->perform($model);

		//override the config options so that we can get the latest
		app('admin_config_factory')->updateConfigOptions();

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return response()->json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');
			return response()->json(array('success' => false, 'error' => $messages['error']));
		}
		else
		{
			$fieldFactory = app('admin_field_factory');
			$columnFactory = app('admin_column_factory');
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
				$this->session->put('administrator_download_response', array('file' => $file, 'headers' => $headers));

				$response['download'] = route('admin_file_download');
			}
			//if it's a redirect, put the url into the redirect key so that javascript can transfer the user
			else if (is_a($result, '\Illuminate\Http\RedirectResponse'))
			{
				$response['redirect'] = $result->getTargetUrl();
			}

			return response()->json($response);
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
		if (config('administrator.use_dashboard'))
		{
			//set the layout dashboard
			$this->layout->dashboard = true;

			//set the layout content
			$this->layout->content = view(config('administrator.dashboard_view'));

			return $this->layout;
		}
		//else we should redirect to the menu item
		else
		{
			$configFactory = app('admin_config_factory');
			$home = config('administrator.home_page');

			//first try to find it if it's a model config item
			$config = $configFactory->make($home);

			if (!$config)
			{
				throw new \InvalidArgumentException("Administrator: " .  trans('administrator::administrator.valid_home_page'));
			}
			else if ($config->getType() === 'model')
			{
				return redirect()->route('admin_index', array($config->getOption('name')));
			}
			else if ($config->getType() === 'settings')
			{
				return redirect()->route('admin_settings', array($config->getOption('name')));
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
		$dataTable = app('admin_datatable');

		//get the sort options and filters
		$page = $this->request->input('page', 1);
		$sortOptions = $this->request->input('sortOptions', array());
		$filters = $this->request->input('filters', array());

		//return the rows
		return response()->json($dataTable->getRows(app('db'), $filters, $page, $sortOptions));
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
		$fieldFactory = app('admin_field_factory');
		$response = array();

		//iterate over the supplied constrained fields
		foreach ($this->request->input('fields', array()) as $field)
		{
			//get the constraints, the search term, and the currently-selected items
			$constraints = array_get($field, 'constraints', array());
			$term = array_get($field, 'term', array());
			$type = array_get($field, 'type', false);
			$fieldName = array_get($field, 'field', false);
			$selectedItems = array_get($field, 'selectedItems', false);

			$response[$fieldName] = $fieldFactory->updateRelationshipOptions($fieldName, $type, $constraints, $selectedItems, $term);
		}

		return response()->json($response);
	}

	/**
	 * The GET method that displays a file field's file
	 *
	 * @return Image / File
	 */
	public function displayFile()
	{
		//get the stored path of the original
		$path = $this->request->input('path');
		$data = File::get($path);
		$file = new SFile($path);

		$headers = array(
			'Content-Type' => $file->getMimeType(),
			'Content-Length' => $file->getSize(),
			'Content-Disposition' => 'attachment; filename="' . $file->getFilename() . '"'
		);

		return response()->make($data, 200, $headers);
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
		$fieldFactory = app('admin_field_factory');

		//get the model and the field object
		$field = $fieldFactory->findField($fieldName);

		return response()->JSON($field->doUpload());
	}

	/**
	 * The GET method that runs when a user needs to download a file
	 *
	 * @return JSON
	 */
	public function fileDownload()
	{
		if ($response = $this->session->get('administrator_download_response'))
		{
			$this->session->forget('administrator_download_response');
			$filename = substr($response['headers']['content-disposition'][0], 22, -1);

			return response()->download($response['file'], $filename, $response['headers']);
		}
		else
		{
			return redirect()->back();
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
		$dataTable = app('admin_datatable');

		//get the inputted rows and the model rows
		$rows = (int) $this->request->input('rows', 20);
		$dataTable->setRowsPerPage(app('session.store'), 0, $rows);

		return response()->JSON(array('success' => true));
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
		$this->layout->content = view($page);

		return $this->layout;
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
		$this->layout->content = view("administrator::settings");

		return $this->layout;
	}

	/**
	 * POST save settings method that accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
	 *
	 * @return JSON
	 */
	public function settingsSave()
	{
		$config = app('itemconfig');
		$save = $config->save($this->request, app('admin_field_factory')->getEditFields());

		if (is_string($save))
		{
			return response()->json(array(
				'success' => false,
				'errors' => $save,
			));
		}
		else
		{
			//override the config options so that we can get the latest
			app('admin_config_factory')->updateConfigOptions();

			return response()->json(array(
				'success' => true,
				'data' => $config->getDataModel(),
				'actions' => app('admin_action_factory')->getActionsOptions(),
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
		$config = app('itemconfig');
		$actionFactory = app('admin_action_factory');
		$actionName = $this->request->input('action_name', false);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName);
		$data = $config->getDataModel();
		$result = $action->perform($data);

		//override the config options so that we can get the latest
		app('admin_config_factory')->updateConfigOptions();

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return response()->json(array('success' => false, 'error' => $result));
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');

			return response()->json(array('success' => false, 'error' => $messages['error']));
		}
		else
		{
			$response = array('success' => true, 'actions' => $actionFactory->getActionsOptions(true));

			//if it's a download response, flash the response to the session and return the download link
			if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse'))
			{
				$file = $result->getFile()->getRealPath();
				$headers = $result->headers->all();
				$this->session->put('administrator_download_response', array('file' => $file, 'headers' => $headers));

				$response['download'] = route('admin_file_download');
			}
			//if it's a redirect, put the url into the redirect key so that javascript can transfer the user
			else if (is_a($result, '\Illuminate\Http\RedirectResponse'))
			{
				$response['redirect'] = $result->getTargetUrl();
			}

			return response()->json($response);
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
		if (in_array($locale, config('administrator.locales')))
		{
			$this->session->put('administrator_locale', $locale);
		}

		return redirect()->back();
	}

	/**
	 * POST method to capture any form request errors
	 *
	 * @param \Illuminate\Http\Request $request
	 */
	protected function resolveDynamicFormRequestErrors(Request $request)
	{
		try {
			$config = app('itemconfig');
			$fieldFactory = app('admin_field_factory');
		} catch (\ReflectionException $e) {
			return null;
		}
		if (array_key_exists('form_request', $config->getOptions())) {
			try {
				$model = $config->getFilledDataModel($request, $fieldFactory->getEditFields(), $request->id);

				$request->merge($model->toArray());
				$formRequestClass = $config->getOption('form_request');
				app($formRequestClass);
			} catch (HttpResponseException $e) {
				//Parses the exceptions thrown by Illuminate\Foundation\Http\FormRequest
				$errorMessages = $e->getResponse()->getContent();
				$errorsArray = json_decode($errorMessages);
				if (!$errorsArray && is_string ( $errorMessages )) {
					return $errorMessages;
				}
				if ($errorsArray) {
					return implode(".", array_dot($errorsArray));
				}
			}
		}
		return null;
	}
}
