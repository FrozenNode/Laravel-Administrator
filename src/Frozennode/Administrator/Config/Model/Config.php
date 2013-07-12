<?php
namespace Frozennode\Administrator\Config\Model;

use Frozennode\Administrator\Config\Config as ConfigBase;
use Frozennode\Administrator\Config\ConfigInterface;
use Frozennode\Administrator\Fields\Factory as FieldFactory;
use Frozennode\Administrator\Actions\Factory as ActionFactory;

/**
 * The Model Config class helps retrieve a model's configuration and provides a reliable pointer for these items
 */
class Config extends ConfigBase implements ConfigInterface {

	/**
	 * The config type
	 *
	 * @var string
	 */
	protected $type = 'model';

	/**
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $defaults = array(
		'filters' => array(),
		'permission' => true,
		'action_permissions' => array(
			'create' => true,
			'delete' => true,
			'update' => true,
			'view' => true,
		),
		'actions' => array(),
		'sort' => array(),
		'form_width' => 285,
		'link' => null,
		'rules' => false,
	);

	/**
	 * An instance of the Eloquent model object for this model
	 *
	 * @var Illuminate\Database\Eloquent\Model
	 */
	protected $model;

	/**
	 * The rules array
	 *
	 * @var array
	 */
	protected $rules = array(
		'title' => 'required|string',
		'single' => 'required|string',
		'model' => 'required|string|eloquent',
		'columns' => 'required|array|not_empty',
		'edit_fields' => 'required|array|not_empty',
		'filters' => 'array',
		'permission' => 'callable',
		'action_permissions' => 'array',
		'actions' => 'array',
		'sort' => 'array',
		'form_width' => 'integer',
		'link' => 'callable',
		'rules' => 'array',
	);

	/**
	 * Fetches the data model for a config
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function getDataModel()
	{
		if (!$this->model)
		{
			$name = $this->getOption('model');
			$this->model = new $name;
		}

		return $this->model;
	}

	/**
	 * Sets the data model for a config
	 *
	 * @param Illuminate\Database\Eloquent\Model	$model
	 *
	 * @return  void
	 */
	public function setDataModel($model)
	{
		$this->model = $model;
	}

	/**
	 * Gets a model given an id
	 *
	 * @param id										$id
	 * @param array										$fields
	 * @param array										$columns
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function getModel($id = 0, array $fields, array $columns)
	{
		//if we're getting an existing model, we'll want to first get the edit fields without the relationships loaded
		$originalModel = $model = $this->getDataModel();

		//make sure the edit fields are included
		foreach ($fields as $field => $obj)
		{
			if (!$obj->relationship && !$obj->setter && !array_key_exists($field, $columns))
			{
				$columns[$field] = $model->getTable().'.'.$field;
			}
		}

		//get the model by id
		$model = $model->find($id, $columns);
		$model = $model ? $model : $originalModel;

		//if the model exists, load up the existing related items
		if ($model->exists)
		{
			//make sure the relationships are loaded
			foreach ($fields as $field => $info)
			{
				if ($info->relationship)
				{
					//if this is a belongsToMany, we want to sort our initial values
					if ($info->multipleValues)
					{
						//if a sort_field is provided, use it, otherwise sort by the name field
						if ($info->sortField)
						{
							$relatedItems = $model->{$field}()->orderBy($info->sortField)->get();
						}
						else
						{
							$relatedItems = $model->{$field}()->orderBy($info->nameField)->get();
						}
					}
					else
					{
						$relatedItems = $model->{$field}()->get();
					}

					//get all existing values for this relationship
					if ($relatedItems)
					{
						//the array that holds all the ids of the currently-related items
						$relationsArray = array();

						//the id-indexed array that holds all of the select option data for a relation.
						//this holds the currently-related items and all of the available options
						$autocompleteArray = array();

						//iterate over the items
						foreach ($relatedItems as $item)
						{
							//if this is a mutliple-value type (i.e. HasMany, BelongsToMany), make sure this is an array
							if ($info->multipleValues)
							{
								$relationsArray[] = $item->{$item->getKeyName()};
							}
							else
							{
								$model->setAttribute($field, $item->{$item->getKeyName()});
							}

							//if this is an autocomplete field, we'll need to provide an array of arrays with 'id' and 'text' indexes
							if ($info->autocomplete)
							{
								$autocompleteArray[$item->{$item->getKeyName()}] = array('id' => $item->{$item->getKeyName()}, 'text' => $item->{$info->nameField});
							}
						}

						//if this is a BTM, set the relations array to the property that matches the relationship name
						if ($info->multipleValues)
						{
							$model->{$field} = $relationsArray;
						}

						//set the options attribute
						$model->setAttribute($field.'_options', $info->options);

						//unset the relationships so we only get back what we need
						$model->relationships = array();

						//set the autocomplete array
						if ($info->autocomplete)
						{
							$model->setAttribute($field.'_autocomplete', $autocompleteArray);
						}
					}
					//if there are no values, then just set an empty array
					else
					{
						$model->{$field} = array();
					}
				}

				//if this is a setter field, unset it
				if ($info->setter)
				{
					$model->__unset($field);
				}
			}
		}

		return $model;
	}

	/**
	 * Updates a model with the latest permissions, links, and fields
	 *
	 * @param Eloquent									$model
	 * @param Frozennode\Administrator\Fields\Factory	$fieldFactory
	 * @param Frozennode\Administrator\Actions\Factory	$actionFactory
	 *
	 * @return Eloquent
	 */
	public function updateModel($model, FieldFactory $fieldFactory, ActionFactory $actionFactory)
	{
		$originalModel = $this->getDataModel();

		//set the data model to the active model
		$this->setDataModel($model::find($model->id));

		//include the item link if one was supplied
		if ($link = $this->getModelLink())
		{
			$model->setAttribute('admin_item_link', $link);
		}

		//set up the model with the edit fields new data
		$model->setAttribute('administrator_edit_fields', $fieldFactory->getEditFieldsArrays(true));

		//set up the new actions data
		$model->setAttribute('administrator_actions', $actionFactory->getActions(true));
		$model->setAttribute('administrator_action_permissions', $actionFactory->getActionPermissions(true));

		return $model;
	}

	/**
	 * Saves the model
	 *
	 * @param Illuminate\Http\Request	$input
	 * @param array						$fields
	 * @param array						$actionPermissions
	 * @param int						$id
	 *
	 * @return mixed	//string if error, true if success
	 */
	public function save(\Illuminate\Http\Request $input, array $fields, array $actionPermissions = null, $id = 0)
	{
		$model = $this->getDataModel()->find($id);

		//fetch the proper model so we don't have to deal with any extra attributes
		if (!$model)
		{
			$model = $this->getDataModel();
		}

		//make sure the user has the proper permissions
		if ($model->exists && !$actionPermissions['update'])
		{
			return "You do not have permission to save this item";
		}
		else if (!$actionPermissions['update'] || !$actionPermissions['create'])
		{
			return "You do not have permission to create this item";
		}

		//fill the model with our input
		$this->fillModel($model, $input, $fields);

		//prepares the $data and $rules arrays
		extract($this->prepareDataAndRules($model));

		//validate the model
		$validation = $this->validateData($data);

		//if a string was kicked back, it's an error, so return it
		if (is_string($validation)) return $validation;

		//save the model
		$model->save();

		//save the relationships
		$this->saveRelationships($model, $fields);

		//set/update the data model
		$this->setDataModel($model);

		return true;
	}

	/**
	 * Sets the proper data attributes and rules arrays depending on whether or not the model exists
	 *
	 * @param $model	Eloquent
	 *
	 * @return array	//'data' and 'rules' indexes both arrays
	 */
	public function prepareDataAndRules($model)
	{
		//fetch the rules if any exist
		$rules = $this->getModelValidationRules();

		//if the model exists, this is an update
		if ($model->exists)
		{
			//only include dirty fields
			$data = $model->getDirty();

			//and validate the fields that are being updated
			$rules = array_intersect_key($rules, $data);
		}
		else
		{
			//otherwise validate everything
			$data = $model->getAttributes();
		}

		return compact('data', 'rules');
	}

	/**
	 * Prepare a model for saving given a post input array
	 *
	 * @param Illuminate\Database\Eloquent\Model	$model
	 * @param Illuminate\Http\Request				$input
	 * @param array									$fields
	 *
	 * @return void
	 */
	public function fillModel(&$model, \Illuminate\Http\Request $input, array $fields)
	{
		//run through the edit fields to see if we need to unset relationships
		foreach ($fields as $field => $object)
		{
			if (!$object->external)
			{
				$object->fillModel($model, $input->get($field, NULL));
			}
			//if this is an "external" field (i.e. it's not a column on this model's table), unset it
			else
			{
				$model->__unset($field);
			}
		}

		//loop through the fields again to unset any setter fields
		foreach ($fields as $field => $object)
		{
			if (($object->setter && $object->type !== 'password') || ($object->type === 'password' && empty($model->{$field})))
			{
				$model->__unset($field);
			}
		}
	}

	/**
	 * Validates the supplied data against the options rules
	 *
	 * @param array		$data
	 *
	 * @param mixed
	 */
	public function validateData(array $data)
	{
		if ($rules = $this->getOption('rules'))
		{
			$this->validator->override($data, $rules);

			//if the validator fails, kick back the errors
			if ($this->validator->fails())
			{
				return implode('. ', $this->validator->messages()->all());
			}
		}

		return true;
	}

	/**
	 * Gets the validation rules for this model
	 *
	 * @return array
	 */
	public function getModelValidationRules()
	{
		$model = $this->getDataModel();
		$optionsRules = $this->getOption('rules');

		//if the 'rules' option was provided for this model, it takes precedent
		if (is_array($optionsRules))
		{
			return $optionsRules;
		}
		//otherwise look for the
		else if ($rules = $this->getModelStaticValidationRules())
		{
			return $rules;
		}
		else
		{
			return array();
		}
	}

	/**
	 * Gets the static rules propery for a model if one exists
	 *
	 * @return mixed
	 */
	public function getModelStaticValidationRules()
	{
		$model = $this->getDataModel();

		return isset($model::$rules) && is_array($model::$rules) ? $model::$rules : false;
	}

	/**
	 * After a model has been saved, this is called to save the relationships
	 *
	 * @param Eloquent		$model
	 * @param array			$fields
	 *
	 * @return void
	 */
	public function saveRelationships(&$model, array $fields)
	{
		//run through the edit fields to see if we need to set relationships
		foreach ($fields as $name => $field)
		{
			if ($field->external)
			{
				$field->fillModel($model, \Input::get($field, NULL));
			}
		}
	}

	/**
	 * Gets a model's link if one was provided, substituting for field names with this format: (:field_name)
	 *
	 * @return mixed
	 */
	public function getModelLink()
	{
		$linkCallback = $this->getOption('link');

		if ($linkCallback && is_callable($linkCallback))
		{
			return $linkCallback($this->getDataModel());
		}
		else
		{
			return false;
		}
	}
}
