<?php
use Admin\Libraries\Column;
use Admin\Libraries\ModelHelper;

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
		if (ModelHelper::deleteModel($model))
		{
			return Response::json(array(
				'success' => true,
				'data' => $model->to_array(),
			));
		}
		else
		{
			return Response::json($errorResponse);
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

}