<?php
namespace Frozennode\Administrator\Config;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\Settings\Config as SettingsConfig;
use Frozennode\Administrator\Config\Model\Config as ModelConfig;

class Factory {

	/**
	 * The validator instance
	 *
	 * @var \Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The config instance
	 *
	 * @var \Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The main options array
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The config name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The config type (settings or model)
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The settings page menu prefix
	 *
	 * @var string
	 */
	protected $settingsPrefix = 'settings.';

	/**
	 * The custom view page menu prefix
	 *
	 * @var string
	 */
	protected $pagePrefix = 'page.';

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
	 * @param \Frozennode\Administrator\Validator 	$validator
	 * @param array 								$options
	 */
	public function __construct(Validator $validator, array $options)
	{
		//set the config, and then validate it
		$this->options = $options;
		$this->validator = $validator;
		$validator->override($this->options, $this->rules);

		//if the validator failed, throw an exception
		if ($validator->fails())
		{
			throw new \InvalidArgumentException('There are problems with your administrator.php config: ' . implode('. ', $validator->messages()->all()));
		}
	}

	/**
	 * Makes a config instance given an input string
	 *
	 * @param string	$name
	 * @param string	$primary	//if true, this is the primary itemconfig object and we want to store the instance
	 *
	 * @return mixed
	 */
	public function make($name, $primary = false)
	{
		//set the name so we can rebuild the config later if necessary
		$this->name = $primary ? $name : $this->name;

		//search the config menu for our item
		$options = $this->searchMenu($name);

		//return the config object if the file/array was found, or false if it wasn't
		$config = $options ? $this->getItemConfigObject($options) : ($this->type === 'page' ? true : false);

		//set the primary config
		$this->config = $primary ? $config : $this->config;

		//return the config object (or false if it fails to build)
		return $config;
	}

	/**
	 * Updates the current item config's options
	 *
	 * @return void
	 */
	public function updateConfigOptions()
	{
		//search the config menu for our item
		$options = $this->searchMenu($this->name);

		//override the config's options
		$this->getConfig()->setOptions($options);
	}

	/**
	 * Gets the current config item
	 *
	 * @return \Frozennode\Administrator\Config\ConfigInterface
	 */
	public function getConfig()
	{
		return $this->config;
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
		//if the name is prefixed with the settings prefix
		if (strpos($name, $this->settingsPrefix) === 0)
		{
			return $this->type = 'settings';
		}
		//otherwise if the name is prefixed with the page prefix
		elseif (strpos($name, $this->pagePrefix) === 0)
		{
			return $this->type = 'page';
		}
		//otherwise it's a model
		else
		{
			return $this->type = 'model';
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
		//parse the type based on the config name if this is the top-level item
		if ($menu === false)
		{
			$this->parseType($name);
		}

		$config = false;
		$menu = $menu ? $menu : $this->options['menu'];

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
	public function getPagePrefix()
	{
		return $this->pagePrefix;
	}

	/**
	 * Gets the prefix for the currently-searched item
	 */
	public function getPrefix()
	{
		if ($this->type === 'settings')
		{
			return $this->settingsPrefix;
		}
		else if ($this->type === 'page')
		{
			return $this->pagePrefix;
		}

		return '';
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
		$path = $this->type === 'settings' ? $this->options['settings_config_path'] : $this->options['model_config_path'];
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
	 * @param array		$options
	 *
	 * @return \Frozennode\Administrator\Config\ConfigInterface
	 */
	public function getItemConfigObject(array $options)
	{
		if ($this->type === 'settings')
		{
			return new SettingsConfig($this->validator, $options);
		}
		else
		{
			return new ModelConfig($this->validator, $options);
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
			//set the options var
			$options = require $path;

			//add the name in
			$options['name'] = $name;

			return $options;
		}

		return false;
	}
}