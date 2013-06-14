<?php
namespace Frozennode\Administrator;

use Illuminate\Support\Facades\Config;;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use \Exception;

/**
 * The ModelConfig class helps retrieve a model's configuration and provides a reliable pointer for these items
 */
class SettingsConfig {

	/**
	 * The settings name prefix
	 *
	 * @var string
	 */
	static $prefix = 'settings.';

	/**
	 * The storage path
	 *
	 * @var string
	 */
	public $storagePath;

	/**
	 * The settings page's title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The settings page's name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Holds the edit fields data
	 *
	 * @var array
	 */
	public $edit;

	/**
	 * Holds the validation rules
	 *
	 * @var array
	 */
	public $rules;

	/**
	 * Holds the before save callback
	 *
	 * @var function
	 */
	public $beforeSave;

	/**
	 * Holds the actions data
	 *
	 * @var array
	 */
	public $actions;

	/**
	 * Holds the settings data
	 *
	 * @var array
	 */
	public $data;


	/**
	 * The constructor sets the values using the supplied config array
	 *
	 * @param array		$config 	//the array of options provide in each model's config
	 */
	public function __construct($config)
	{
		//set the class properties for the items which we know to exist
		$this->storagePath = storage_path() . '/administrator_settings/';
		$this->title = array_get($config, 'title');
		$this->name = array_get($config, 'name');
		$this->edit = array_get($config, 'edit_fields');
		$this->rules = array_get($config, 'rules', array());
		$this->beforeSave = array_get($config, 'before_save', function(){});
		$this->actions = array_get($config, 'actions');

		//fetch the meaningful information for actions
		$this->actions = Action::getActions($this);
		$this->fetchData();
	}

	/**
	 * Takes a settings config name (e.g. 'settings.site') and returns false if it can't be found or a new SettingsConfig instance if it can
	 *
	 * @param string	$settingsName 		//the settings config/uri name
	 *
	 * @return false|SettingsConfig object
	 */
	public static function get($settingsName)
	{
		//if the name doesn't begin with 'settings.', this isn't a settings config
		if (strpos($settingsName, static::$prefix) !== 0)
		{
			return false;
		}

		//first we need to find the settings config (if it exists)
		if (!$config = static::find($settingsName))
		{
			return false;
		}

		//now that we have the config, we can begin to check if all of the required fields are provided

		//but first we have to check if the user has permission to access this settings page
		$permission = array_get($config, 'permission');

		if (is_callable($permission) && !$permission())
		{
			return false;
		}

		//if the title isn't provided, throw an exception
		if (!is_string(array_get($config, 'title')))
		{
			throw new Exception("Administrator: " .  trans('administrator::administrator.valid_title'));
		}

		//check if the edit fields array was provided
		if (!is_array(array_get($config, 'edit_fields')))
		{
			throw new Exception("Administrator: " .  trans('administrator::administrator.valid_edit'));
		}

		//now we can instantiate the object
		return new static($config);
	}

	/**
	 * Finds a settings config given a name from the menu option in the main config
	 *
	 * @param string		$settingsName
	 *
	 * @return false|array	//If found, an array of (unvalidated) config options will be returned
	 */
	public static function find($settingsName)
	{
		//first let's grab the menu and settings_config_path options
		$menu = Config::get('administrator::administrator.menu', null);
		$settingsConfigPath = Config::get('administrator::administrator.settings_config_path', null);
		$settingsName = substr($settingsName, strlen(static::$prefix));

		//if the menu option isn't an array or if it doesn't have any values, throw an exception since it's a required option
		if (!is_array($menu) || !sizeof($menu))
		{
			throw new Exception("Administrator: " .  trans('administrator::administrator.valid_menu'));
		}

		//if the settings config path isn't a string or if the directory doesn't exist, throw an exception
		if (!is_string($settingsConfigPath) || !is_dir($settingsConfigPath))
		{
			throw new Exception("Administrator: " .  trans('administrator::administrator.config_path'));
		}

		//now we loop through the menu and try to find our guy
		$settingsConfigPath = rtrim($settingsConfigPath, '/') . '/';
		$config = static::searchMenu($menu, $settingsConfigPath, $settingsName);

		//return the config if it was set
		return is_array($config) ? $config : false;
	}

	/**
	 * Recursively searches the menu array for the desired settings config name
	 *
	 * @param array		$menu
	 * @param string	$settingsConfigPath
	 * @param string	$settingsName
	 *
	 * @return false|array	//If found, an array of (unvalidated) config options will returned
	 */
	private static function searchMenu($menu, $settingsConfigPath, $settingsName)
	{
		$config = false;

		//iterate over all the items in the menu array
		foreach ($menu as $key => $item)
		{
			//if the item is a string, try to find the config file
			if (is_string($item) && $item === static::$prefix . $settingsName)
			{
				$path = $settingsConfigPath . $settingsName . '.php';

				//check that this is a legitimate file
				if (is_file($path))
				{
					//set the config var
					$config = require $path;

					//add the settings's name in
					$config['name'] = $settingsName;
				}
			}
			//if the item is an array, recursively run this method on it
			else if (is_array($item))
			{
				$config = static::searchMenu($item, $settingsConfigPath, $settingsName);
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
	 * Fetches the data for this settings config and stores it in the data property
	 */
	public function fetchData()
	{
		//attempt to make the storage path if it doesn't already exist
		if (!is_dir($this->storagePath))
		{
			mkdir($this->storagePath);
		}

		//check if the storage path is writable
		if (!is_writable($this->storagePath))
		{
			throw new Exception("Administrator: " .  trans('administrator::administrator.storage_path_permissions'));
		}

		//set up the blank data
		$data = array();

		foreach ($this->edit as $field => $info)
		{
			$data[$field] = null;
		}

		//try to fetch the JSON file
		$file = $this->storagePath . $this->name . '.json';

		if (file_exists($file))
		{
			$json = file_get_contents($file);
			$saveData = json_decode($json);

			//run through the saveData and update the associated fields that we populated from the edit fields
			foreach ($saveData as $field => $value)
			{
				if (array_key_exists($field, $data))
				{
					$data[$field] = $value;
				}
			}
		}

		$this->data = $data;
	}

	/**
	 * Attempts to save a settings page
	 *
	 * @return Response
	 */
	public function save()
	{
		$data = array();

		//iterate over the edit fields to only fetch the important items
		foreach ($this->edit as $field => $info)
		{
			$data[$field] = Input::get($field);

			//make sure the bool field is set correctly
			if ($info['type'] === 'bool')
			{
				$data[$field] = $data[$field] === 'true' || $data[$field] === '1' ? 1 : 0;
			}
		}

		//validate the model
		$validator = Validator::make($data, $this->rules);

		//if the validator fails, kick back the errors
		if ($validator->fails())
		{
			return Response::json(array(
				'success' => false,
				'errors' => $validator->errors()->all(),
			));
		}
		else
		{
			//run the beforeSave function if provided
			$beforeSave = $this->beforeSave;

			if (is_callable($beforeSave))
			{
				$bs = $beforeSave($data);

				//if a string is returned, assume it's an error and kick it back
				if (is_string($bs))
				{
					return Response::json(array(
						'success' => false,
						'errors' => $bs,
					));
				}
			}

			//Save the JSON data
			$this->putToJSON($data);

			return Response::json(array(
				'success' => true,
				'data' => $data,
			));
		}
	}

	/**
	 * Puts the data contents into the json file
	 *
	 * @param array		$data
	 */
	public function putToJSON($data)
	{
		file_put_contents($this->storagePath . $this->name . '.json', json_encode($data));
	}
}