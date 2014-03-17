<?php namespace Frozennode\Administrator\Routing\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Routing\Controller;
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
use Frozennode\Administrator\Field\Field;

/**
 * Handles all requests related to managing the data models
 */
class Admin extends Base {

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
		$fieldFactory = App::make('admin.field.factory');
		$actionFactory = App::make('admin.action.factory');
		$columnFactory = App::make('admin.column.factory');
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

			$response = $actionPermissions['view'] ? Response::json($model) : Response::json([
				'success' => false,
				'errors' => "You do not have permission to view this item",
			]);

			//set the Vary : Accept header to avoid the browser caching the json response
			return $response->header('Vary', 'Accept');
		}
		else
		{
			$view = View::make("administrator::index", [
				'itemId' => $itemId,
			]);

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
		$fieldFactory = App::make('admin.field.factory');
		$actionFactory = App::make('admin.action.factory');
		$save = $config->save(App::make('request'), $fieldFactory->getEditFields(), $actionFactory->getActionPermissions(), $id);

		if (is_string($save))
		{
			return Response::json([
				'success' => false,
				'errors' => $save,
			]);
		}
		else
		{
			//override the config options so that we can get the latest
			App::make('admin.config.factory')->updateConfigOptions();

			//grab the latest model data
			$columnFactory = App::make('admin.column.factory');
			$fields = $fieldFactory->getEditFields();
			$model = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));

			if ($model->exists)
			{
				$model = $config->updateModel($model, $fieldFactory, $actionFactory);
			}

			return Response::json([
				'success' => true,
				'data' => $model->toArray(),
			]);
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
		$actionFactory = App::make('admin.action.factory');
		$baseModel = $config->getDataModel();
		$model = $baseModel::find($id);
		$errorResponse = [
			'success' => false,
			'error' => "There was an error deleting this item. Please reload the page and try again.",
		];

		//if the model or the id don't exist, send back an error
		$permissions = $actionFactory->getActionPermissions();

		if (!$model->exists || !$permissions['delete'])
		{
			return Response::json($errorResponse);
		}

		//delete the model
		if ($model->delete())
		{
			return Response::json([
				'success' => true,
			]);
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
		$actionFactory = App::make('admin.action.factory');
		$actionName = Input::get('action.name', false);
		$dataTable = App::make('admin.grid');

		//get the sort options and filters
		$page = Input::get('page', 1);
		$sortOptions = Input::get('sortOptions', []);
		$filters = Input::get('filters', []);

		//get the prepared query options
		$prepared = $dataTable->prepareQuery(App::make('db'), $page, $sortOptions, $filters);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName, true);
		$result = $action->perform($prepared['query']);

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return Response::json(['success' => false, 'error' => $result]);
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');

			return Response::json(['success' => false, 'error' => $messages['error']]);
		}
		else
		{
			$response = ['success' => true];

			//if it's a download response, flash the response to the session and return the download link
			if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse'))
			{
				$file = $result->getFile()->getRealPath();
				$headers = $result->headers->all();
				Session::put('administrator_download_response', ['file' => $file, 'headers' => $headers]);

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
		$actionFactory = App::make('admin.action.factory');
		$model = $config->getDataModel();
		$model = $model::find($id);
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = $actionFactory->getByName($actionName);
		$result = $action->perform($model);

		//override the config options so that we can get the latest
		App::make('admin.config.factory')->updateConfigOptions();

		//if the result is a string, return that as an error.
		if (is_string($result))
		{
			return Response::json(['success' => false, 'error' => $result]);
		}
		//if it's falsy, return the standard error message
		else if (!$result)
		{
			$messages = $action->getOption('messages');
			return Response::json(['success' => false, 'error' => $messages['error']]);
		}
		else
		{
			$fieldFactory = App::make('admin.field.factory');
			$columnFactory = App::make('admin.column.factory');
			$fields = $fieldFactory->getEditFields();
			$model = $config->getModel($id, $fields, $columnFactory->getIncludedColumns($fields));

			if ($model->exists)
			{
				$model = $config->updateModel($model, $fieldFactory, $actionFactory);
			}

			$response = ['success' => true, 'data' => $model->toArray()];

			//if it's a download response, flash the response to the session and return the download link
			if (is_a($result, 'Symfony\Component\HttpFoundation\BinaryFileResponse'))
			{
				$file = $result->getFile()->getRealPath();
				$headers = $result->headers->all();
				Session::put('administrator_download_response', ['file' => $file, 'headers' => $headers]);

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
	 * Gets the database results for the current model
	 *
	 * @param string		$modelName
	 *
	 * @return array of rows
	 */
	public function results($modelName)
	{
		$dataTable = App::make('admin.grid');

		//get the sort options and filters
		$page = Input::get('page', 1);
		$sortOptions = Input::get('sortOptions', []);
		$filters = Input::get('filters', []);

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
		$fieldFactory = App::make('admin.field.factory');
		$response = [];

		//iterate over the supplied constrained fields
		foreach (Input::get('fields', []) as $field)
		{
			//get the constraints, the search term, and the currently-selected items
			$constraints = array_get($field, 'constraints', []);
			$term = array_get($field, 'term', []);
			$type = array_get($field, 'type', false);
			$fieldName = array_get($field, 'field', false);
			$selectedItems = array_get($field, 'selectedItems', false);

			$response[$fieldName] = $fieldFactory->updateRelationshipOptions($fieldName, $type, $constraints, $selectedItems, $term);
		}

		return Response::json($response);
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
		$dataTable = App::make('admin.grid');

		//get the inputted rows and the model rows
		$rows = (int) Input::get('rows', 20);
		$dataTable->setRowsPerPage(App::make('session.store'), 0, $rows);

		return Response::JSON(['success' => true]);
	}
}