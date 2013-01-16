<?php
use Admin\Libraries\Column;
use Admin\Libraries\ModelHelper;
use Admin\Libraries\Action;
use Admin\Libraries\Fields\Field;

/**
 * Handles all requests related to managing the data models
 */
class Administrator_Admin_Controller extends Controller
{

	var $layout = "administrator::layouts.default";


	/**
	 * The main view for any of the data models
	 *
	 * @param string	$modelName
	 *
	 * @return Response
	 */
	public function action_index($modelName)
	{
		//first we get the data model
		$model = ModelHelper::getModelInstance($modelName);

		$view = View::make("administrator::index",
			array(
				"modelName" => $modelName,
			)
		);

		//set the layout content and title
		$this->layout->modelName = $modelName;
		$this->layout->content = $view;
	}


	/**
	 * Gets the item edit page / information
	 *
	 * @param string	$modelName
	 * @param mixed		$itemId
	 */
	public function action_item($modelName, $itemId = false)
	{
		//try to get the object
		$model = ModelHelper::getModel($modelName, $itemId, true);

		//if it's ajax, we just return the item information as json
		//otherwise we load up the index page and dump values into
		if (Request::ajax())
		{
			return eloquent_to_json($model);
		}
		else
		{
			$view = View::make("administrator::index", array(
				"modelName" => $modelName,
				"model" => $model,
			));

			//set the layout content and title
			$this->layout->modelName = $modelName;
			$this->layout->content = $view;
		}
	}

	/**
	 * POST save method that accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
	 *
	 * @param string	$modelName
	 * @param int		$id
	 *
	 * @return JSON
	 */
	public function action_save($modelName, $id = false)
	{
		$model = ModelHelper::getModel($modelName, $id);

		//fill the model with our input
		ModelHelper::fillModel($model);

		$rules = isset($model::$rules) ? $model::$rules : array();

		//if the model exists, this is an update
		if ($model->exists)
		{
			//so only include dirty fields
			$data = $model->get_dirty();

			//and validate the fields that are being updated
			$rules = array_intersect_key($rules, $data);
		}
		else
		{
			//otherwise validate everything
			$data = $model->attributes;
		}

		//validate the model
		$validator = Validator::make($data, $rules);

		if ($validator->fails())
		{
			return Response::json(array(
				'success' => false,
				'errors' => $validator->errors->all(),
			));
		}
		else
		{
			$model->save();

			//Save the relationships
			ModelHelper::saveRelationships($model);

			return Response::json(array(
				'success' => true,
				'data' => $model->to_array(),
			));
		}
	}

	/**
	 * POST delete method that accepts data via JSON POST and either saves an old
	 *
	 * @param string	$modelName
	 * @param int		$id
	 *
	 * @return JSON
	 */
	public function action_delete($modelName, $id)
	{
		$model = ModelHelper::getModel($modelName, $id);
		$errorResponse = array(
			'success' => false,
			'error' => "There was an error deleting this item. Please reload the page and try again.",
		);

		//if the model or the id don't exist, send back 404
		if (!$model->exists)
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
	 * @param string	$modelName
	 * @param int		$id
	 *
	 * @return JSON
	 */
	public function action_custom_action($modelName, $id)
	{
		$model = ModelHelper::getModel($modelName, $id, false, true);
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = Action::getByName($model, $actionName);
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

	public function action_dashboard()
	{
		//set the layout content and title
		$this->layout->modelName = "";
		$this->layout->content = View::make("administrator::dashboard");
	}

	/**
	 * Gets the item edit page / information
	 *
	 * @param string	$modelName
	 *
	 * @return array of rows
	 */
	public function action_results($modelName)
	{
		//try to get the object
		$model = ModelHelper::getModel($modelName);

		//get the sort options and filters
		$sortOptions = Input::get('sortOptions', array());
		$filters = Input::get('filters', array());

		//return the rows
		return Response::json(ModelHelper::getRows($model, $sortOptions, $filters));
	}

	/**
	 * Gets a list of related items given constraints
	 *
	 * @param string	$modelName
	 *
	 * @return array of objects [{id: string} ... {1: 'name'}, ...]
	 */
	public function action_update_options($modelName)
	{
		//try to get the object
		$model = ModelHelper::getModel($modelName);

		//get the constraints, the search term, and the currently-selected items
		$constraints = Input::get('constraints', array());
		$term = Input::get('term', '');
		$type = Input::get('type', false);
		$field = Input::get('field', false);
		$selectedItems = Input::get('selectedItems', false);

		//return the rows
		return Response::json(ModelHelper::updateRelationshipOptions($model, $field, $type, $constraints, $selectedItems, $term));
	}

	/**
	 * The POST method that runs when a user uploads an image on an image field
	 *
	 * @param string	$modelName
	 * @param string	$fieldName
	 *
	 * @return JSON
	 */
	public function action_image_upload($modelName, $fieldName)
	{
		//get the model and the field object
		$model = ModelHelper::getModel($modelName);
		$field = Field::findField($model, $fieldName);

		return Response::JSON($field->doUpload());
	}

	/**
	 * The POST method for setting a user's rows per page
	 *
	 * @param string	$modelName
	 * @param string	$fieldName
	 *
	 * @return JSON
	 */
	public function action_rows_per_page($modelName)
	{
		//get the model
		$model = ModelHelper::getModel($modelName);

		//get the inputted rows and the model rows
		$rows = (int) Input::get('rows', 20);
		$per_page = $model->per_page() ? $model->per_page() : 20;

		if ($rows <= 0 || $rows > 100)
		{
			$rows = $per_page;
		}

		Session::put('administrator_' . $modelName . '_rows_per_page', $rows);

		return Response::JSON(array('success' => true));
	}

}