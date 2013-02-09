<?php
namespace Admin\Libraries;

use \Config;
use \Exception;

/**
 * The ModelConfig class helps retrieve a model's configuration and provides a reliable pointer for these items
 */
class ModelConfig {

	/**
	 * The model's title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The singular name
	 *
	 * @var string
	 */
	public $single;

	/**
	 * The model name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * An instance of the Eloquent model object for this model
	 *
	 * @var Eloquent
	 */
	public $model;

	/**
	 * The expand width of the form for this model
	 *
	 * @var int
	 */
	public $formWidth = 285;

	/**
	 * The number of rows per page to display for this model
	 *
	 * @var string
	 */
	public $rowsPerPage = 20;

	/**
	 * Holds the sort options
	 *
	 * @var array
	 */
	public $sort;

	/**
	 * Holds the columns data
	 *
	 * @var array
	 */
	public $columns;

	/**
	 * Holds the filters data
	 *
	 * @var array
	 */
	public $filters;

	/**
	 * Holds the edit fields data
	 *
	 * @var array
	 */
	public $edit;

	/**
	 * Holds the actions data
	 *
	 * @var array
	 */
	public $actions;

	/**
	 * Holds the action permissions info
	 *
	 * @var array
	 */
	public $actionPermissions = array(
		'create' => true,
		'delete' => true,
		'update' => true,
	);

	/**
	 * If provided, this holds the callback for creating the link for an item
	 *
	 * @var function
	 */
	public $linkCallback;



	/**
	 * The constructor takes a field, column array, and the associated Eloquent model
	 *
	 * @param array		$config 	//the array of options provide in each model's config
	 */
	public function __construct($config)
	{
		//set the class properties for the items which we know to exist
		$this->title = array_get($config, 'title');
		$this->single = array_get($config, 'single');
		$this->model = array_get($config, 'model');
		$this->columns = array_get($config, 'columns');
		$this->actions = array_get($config, 'actions');
		$this->edit = array_get($config, 'edit_fields');
		$this->filters = array_get($config, 'filters', array());
		$this->name = array_get($config, 'model_name');

		//fetch the meaningful information for columns and actions
		//we won't do the same for edit fields and filters because that information is not always persistent across a request
		$this->columns = Column::getColumns($this);
		$this->actions = Action::getActions($this);

		//copy $this->model because of php syntax issues
		$model = $this->model;

		//now set the properties for other items

		//form width option
		$formWidth = array_get($config, 'form_width', $this->formWidth);

		if (!is_int($formWidth) || $formWidth < $this->formWidth)
		{
			$formWidth = $this->formWidth;
		}

		$this->formWidth = $formWidth;

		//sort options
		$this->sort = array_get($config, 'sort', array());
		$this->setSort();

		//get the rows per page
		$this->setRowsPerPage();

		//grab the model link callback
		$linkCallback = array_get($config, 'link');
		$this->linkCallback = is_callable($linkCallback) ? $linkCallback : null;

		//grab the action permissions, if supplied
		$actionPermissions = array_get($config, 'action_permissions', array());
		$create = array_get($actionPermissions, 'create');
		$delete = array_get($actionPermissions, 'delete');
		$update = array_get($actionPermissions, 'update');

		$this->actionPermissions['create'] = is_callable($create) ? $create() : true;
		$this->actionPermissions['delete'] = is_callable($delete) ? $delete() : true;
		$this->actionPermissions['update'] = is_callable($update) ? $update() : true;
	}

	/**
	 * Takes a the key/value of the columns array and the associated model and returns an instance of the column or false
	 *
	 * @param string|int	$modelName 		//the model config/uri name
	 *
	 * @return false|Field object
	 */
	public static function get($modelName)
	{
		//first we need to find the model's config (if it exists)
		if (!$config = static::find($modelName))
		{
			return false;
		}

		//now that we have the config, we can begin to check if all of the required fields are provided

		//but first we have to check if the user has permission to access this model
		$permission = array_get($config, 'permission');

		if (is_callable($permission) && !$permission())
		{
			return false;
		}

		//if the title or single names are provided, throw an exception
		if (!is_string(array_get($config, 'title')) || !is_string(array_get($config, 'single')))
		{
			throw new Exception("Administrator: " .  __('administrator::administrator.valid_title'));
		}

		//get an instance of the model
		$modelName = array_get($config, 'model');

		if (!is_string($modelName))
		{
			throw new Exception("Administrator: " .  __('administrator::administrator.valid_model'));
		}

		//grab an instance of the Eloquent model
		$config['model'] = ModelHelper::getModelInstance($modelName);

		//check if the required columns array was provided
		if (!is_array(array_get($config, 'columns')))
		{
			throw new Exception("Administrator: " .  __('administrator::administrator.valid_columns'));
		}

		//check if the edit fields array was provided
		if (!is_array(array_get($config, 'edit_fields')))
		{
			throw new Exception("Administrator: " .  __('administrator::administrator.valid_edit'));
		}

		//now we can instantiate the object
		return new static($config);
	}

	/**
	 * Finds a model given a name from the menu option in the main config
	 *
	 * @param string		$modelName
	 *
	 * @return false|array	//If found, an array of (unvalidated) config options will returned
	 */
	private static function find($modelName)
	{
		//first let's grab the menu and model_config_path options
		$menu = Config::get('administrator::administrator.menu', null);
		$modelConfigPath = Config::get('administrator::administrator.model_config_path', null);

		//if the menu option isn't an array or if it doesn't have any values, throw an exception since it's a required option
		if (!is_array($menu) || !sizeof($menu))
		{
			throw new Exception("Administrator: " .  __('administrator::administrator.valid_menu'));
		}

		//if the model config path isn't a string or if the directory doesn't exist, throw an exception
		if (!is_string($modelConfigPath) || !is_dir($modelConfigPath))
		{
			throw new Exception("Administrator: " .  __('administrator::administrator.config_path'));
		}

		//now we loop through the menu and try to find our guy
		$modelConfigPath = rtrim($modelConfigPath, '/') . '/';
		$config = static::searchMenu($menu, $modelConfigPath, $modelName);

		//return the config if it was set
		return is_array($config) ? $config : false;
	}

	/**
	 * Recursively searches the menu array for the desired model name
	 *
	 * @param
	 *
	 * @return false|array	//If found, an array of (unvalidated) config options will returned
	 */
	private static function searchMenu($menu, $modelConfigPath, $modelName)
	{
		$config = false;

		//iterate over all the items in the menu array
		foreach ($menu as $key => $item)
		{
			//if the item is a string, try to find the config file
			if (is_string($item) && $item === $modelName)
			{
				$path = $modelConfigPath . $item . '.php';

				//check that this is a legitimate file
				if (is_file($path))
				{
					//set the config var
					$config = require $path;

					//add the model's name in
					$config['model_name'] = $modelName;
				}
			}
			//if the item is an array, recursively run this method on it
			else if (is_array($item))
			{
				$config = static::searchMenu($item, $modelConfigPath, $modelName);
			}

			//if the config var was set, break the loop
			if (is_array($config))
			{
				break;
			}
		}

		return $config;
	}

	/**
	 * Helper method to set up the sort options
	 *
	 * @param array		$sort
	 */
	public function setSort($sort = null)
	{
		//put the model into a variable so we can call static properties
		$model = $this->model;
		$sort = $sort && is_array($sort) ? $sort : $this->sort;

		//force the sort to be an array
		if (!is_array($sort))
		{
			$sort = array();
		}

		//set the sort values
		$this->sort = array(
			'field' => array_get($sort, 'field', $model::$key),
			'direction' => array_get($sort, 'direction', 'desc'),
		);

		//if the sort direction isn't valid, set it to 'desc'
		if (!in_array($this->sort['direction'], array('asc', 'desc')))
		{
			$this->sort['direction'] = 'desc';
		}
	}

	/**
	 * Helper method to set the number of rows per page for this model
	 *
	 * @param int		$override	//if provided, this will set the session's rows per page value
	 */
	public function setRowsPerPage($override = null)
	{
		if (is_int($override))
		{
			\Session::put('administrator_' . $this->name . '_rows_per_page', $override);
		}

		$globalPerPage = Config::get('administrator::administrator.global_rows_per_page');
		$perPage = \Session::get('administrator_' . $this->name . '_rows_per_page');

		if (!$perPage)
		{
			if ($globalPerPage && is_int($globalPerPage))
			{
				$perPage = $globalPerPage;
			}
			else
			{
				$perPage = 20;
			}
		}

		$this->rowsPerPage = $perPage;
	}

	/**
	 * Gets the menu items indexed by their name with a value of the title
	 *
	 * @param array		$configMenu (used for recursion)
	 *
	 * @return array
	 */
	public static function getMenu($configMenu = null)
	{
		$menu = array();

		if (!$configMenu)
		{
			$configMenu = Config::get('administrator::administrator.menu', null);
		}

		//iterate over the menu to build the
		foreach ($configMenu as $key => $item)
		{
			//if the item is a string, find its config
			if (is_string($item))
			{
				$config = static::find($item);

				if ($config)
				{
					$permission = array_get($config, 'permission');

					if (is_callable($permission) && !$permission())
					{
						continue;
					}

					$menu[$item] = array_get($config, 'title', $item);
				}
			}
			//if the item is an array, recursively run this method on it
			else if (is_array($item))
			{
				$menu[$key] = static::getMenu($item);
			}
		}

		return $menu;
	}

	/**
	 * Gets a model's link if one was provided, substituting for field names with this format: (:field_name)
	 *
	 * @return false|string
	 */
	public function getModelLink($model)
	{
		if ($this->linkCallback)
		{
			$linkCallback = $this->linkCallback;

			return $linkCallback($model);
		}
		else
		{
			return false;
		}
	}
}
