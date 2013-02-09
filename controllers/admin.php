<?php
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
	 * @param ModelConfig	$config
	 *
	 * @return Response
	 */
	public function action_index($config)
	{
		//set the layout content and title
		$this->layout->content = View::make("administrator::index", array('config' => $config));
	}


	/**
	 * Gets the item edit page / information
	 *
	 * @param ModelConfig	$config
	 * @param mixed			$itemId
	 */
	public function action_item($config, $itemId = false)
	{
		//try to get the object
		$model = ModelHelper::getModel($config, $itemId, true);

		//if it's ajax, we just return the item information as json
		//otherwise we load up the index page and dump values into
		if (Request::ajax())
		{
			return eloquent_to_json($model);
		}
		else
		{
			//if the $itemId is false, we can assume this is a request for /new
			//if the user doesn't have the proper permissions to create, redirect them back to the model page
			if (!$itemId && !$config->actionPermissions['create'])
			{
				return Redirect::to_route('admin_index', array($config->name));
			}

			$view = View::make("administrator::index", array(
				'config' => $config,
				'model' => $model,
			));

			//set the layout content and title
			$this->layout->content = $view;
		}
	}

	/**
	 * POST save method that accepts data via JSON POST and either saves an old item (if id is valid) or creates a new one
	 *
	 * @param ModelConfig	$config
	 * @param int			$id
	 *
	 * @return JSON
	 */
	public function action_save($config, $id = false)
	{
		$model = ModelHelper::getModel($config, $id, false, false, true);

		//fill the model with our input
		ModelHelper::fillModel($config, $model);

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
			$data = $model->get_dirty();

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
			ModelHelper::saveRelationships($config, $model);

			return Response::json(array(
				'success' => true,
				'data' => $model->to_array(),
			));
		}
	}

	/**
	 * POST delete method that accepts data via JSON POST and either saves an old
	 *
	 * @param ModelConfig	$config
	 * @param int			$id
	 *
	 * @return JSON
	 */
	public function action_delete($config, $id)
	{
		$model = ModelHelper::getModel($config, $id);
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
	 * @param ModelConfig	$config
	 * @param int			$id
	 *
	 * @return JSON
	 */
	public function action_custom_action($config, $id)
	{
		$model = ModelHelper::getModel($config, $id, false, true);
		$actionName = Input::get('action_name', false);

		//get the action and perform the custom action
		$action = Action::getByName($config, $actionName);
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

	/**
	 * Shows the dashboard page
	 *
	 * @return Response
	 */
	public function action_dashboard()
	{
		//set the layout content and title
		$this->layout->content = View::make("administrator::dashboard");
	}

	/**
	 * Gets the item edit page / information
	 *
	 * @param ModelConfig	$config
	 *
	 * @return array of rows
	 */
	public function action_results($config)
	{
		//get the sort options and filters
		$sortOptions = Input::get('sortOptions', array());
		$filters = Input::get('filters', array());

		//return the rows
		return Response::json(ModelHelper::getRows($config, $sortOptions, $filters));
	}

	/**
	 * Gets a list of related items given constraints
	 *
	 * @param ModelConfig	$config
	 *
	 * @return array of objects [{id: string} ... {1: 'name'}, ...]
	 */
	public function action_update_options($config)
	{
		//get the constraints, the search term, and the currently-selected items
		$constraints = Input::get('constraints', array());
		$term = Input::get('term', '');
		$type = Input::get('type', false);
		$field = Input::get('field', false);
		$selectedItems = Input::get('selectedItems', false);

		//return the rows
		return Response::json(ModelHelper::updateRelationshipOptions($config, $field, $type, $constraints, $selectedItems, $term));
	}

	/**
	 * The POST method that runs when a user uploads an image on an image field
	 *
	 * @param ModelConfig	$config
	 * @param string	$fieldName
	 *
	 * @return JSON
	 */
	public function action_image_upload($config, $fieldName)
	{
		//get the model and the field object
		$field = Field::findField($config, $fieldName);

		return Response::JSON($field->doUpload());
	}

	/**
	 * The POST method for setting a user's rows per page
	 *
	 * @param ModelConfig	$config
	 *
	 * @return JSON
	 */
	public function action_rows_per_page($config)
	{
		//get the inputted rows and the model rows
		$rows = (int) Input::get('rows', 20);
		$config->setRowsPerPage($rows);

		return Response::JSON(array('success' => true));
	}

}