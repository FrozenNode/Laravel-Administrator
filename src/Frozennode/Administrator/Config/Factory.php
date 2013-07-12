<?php
namespace Frozennode\Administrator\Config;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\Settings\Config as SettingsConfig;
use Frozennode\Administrator\Config\Model\Config as ModelConfig;

class Factory {

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The main config array
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * The config type (settings or model)
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The config type (settings or model)
	 *
	 * @var string
	 */
	protected $settingsPrefix = 'settings.';

	/**
	 * The rules array
	 *
	 * @var array
	 */
	protected $rules = array(
		'uri' => 'required|string',
		'title' => 'required|string',
		'model_config_path' => 'required|string|directory',
		'settings_config_path' => 'required|string|directory',
		'menu' => 'required|array|not_empty',
		'permission' => 'required|callable',
		'use_dashboard' => 'required',
		'dashboard_view' => 'string',
		'home_page' => 'string',
		'login_path' => 'required|string',
		'login_redirect_key' => 'required|string',
	);

	/**
	 * Create a new config Factory instance
	 *
	 * @param Frozennode\Administrator\Validator 	$validator
	 * @param array 								$config
	 */
	public function __construct(Validator $validator, array $config)
	{
		//set the config, and then validate it
		$this->config = $config;
		$this->validator = $validator;
		$validator->override($this->config, $this->rules);

		//if the validator failed, throw an exception
		if ($validator->fails())
		{
			throw new \InvalidArgumentException('There are problems with your administrator.php config: ' . implode('. ', $validator->messages()->all()) . '.');
		}
	}

	/**
	 * Fetch a config instance given an input string
	 *
	 * @param string	$name
	 *
	 * @return mixed
	 */
	public function make($name)
	{
		//determine if this is a model or settings config
		$this->parseType($name);

		//search the config menu for our item
		$config = $this->searchMenu($name);

		//return the config object if the file/array was found, or false if it wasn't
		return $config ? $this->getItemConfigObject($config) : false;
	}

	/**
	 * Determines whether a string is a model or settings config
	 *
	 * @param string	$name
	 *
	 * @return string
	 */
	public function parseType($name)
	{
		$isSettings = strpos($name, $this->settingsPrefix) !== false;

		//if the name is prefixed with the settings prefix
		if ($isSettings)
		{
			$this->type = 'settings';
		}
		//otherwise it's a model
		else
		{
			$this->type = 'model';
		}
	}

	/**
	 * Recursively searches the menu array for the desired settings config name
	 *
	 * @param string	$name
	 * @param array		$menu
	 *
	 * @return false|array	//If found, an array of (unvalidated) config options will returned
	 */
	public function searchMenu($name, $menu = false)
	{
		$config = false;
		$menu = $menu ? $menu : $this->config['menu'];

		//iterate over all the items in the menu array
		foreach ($menu as $key => $item)
		{
			//if the item is a string, try to find the config file
			if (is_string($item) && $item === $name)
			{
				$config = $this->fetchConfigFile($name);
			}
			//if the item is an array, recursively run this method on it
			else if (is_array($item))
			{
				$config = $this->searchMenu($name, $item);
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
	 * Gets the prefix for the currently-searched item
	 */
	public function getSettingsPrefix()
	{
		return $this->settingsPrefix;
	}

	/**
	 * Gets the prefix for the currently-searched item
	 */
	public function getPrefix()
	{
		return $this->type === 'settings' ? $this->settingsPrefix : '';
	}

	/**
	 * Gets the type for the currently-searched item
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Gets the config directory path for the currently-searched item
	 */
	public function getPath()
	{
		$path = $this->type === 'settings' ? $this->config['settings_config_path'] : $this->config['model_config_path'];
		return rtrim($path, '/') . '/';
	}

	/**
	 * Gets the config rules
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Gets an instance of the config
	 *
	 * @param array		$config
	 *
	 * @return Frozennode\Administrator\Config\ConfigInterface
	 */
	public function getItemConfigObject(array $config)
	{
		if ($this->type === 'settings')
		{
			return new SettingsConfig($this->validator, $config);
		}
		else
		{
			return new ModelConfig($this->validator, $config);
		}
	}

	/**
	 * Fetches a config file given a path
	 *
	 * @param string	$name
	 *
	 * @return mixed
	 */
	public function fetchConfigFile($name)
	{
		$name = str_replace($this->getPrefix(), '', $name);
		$path = $this->getPath() . $name . '.php';

		//check that this is a legitimate file
		if (is_file($path))
		{
			//set the config var
			$config = require $path;

			//add the name in
			$config['name'] = $name;

			return $config;
		}

		return false;
	}
}