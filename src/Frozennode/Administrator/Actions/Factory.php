<?php
namespace Frozennode\Administrator\Actions;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;

class Factory {

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The config instance
	 *
	 * @var Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The actions array
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * The action permissions array
	 *
	 * @var array
	 */
	protected $actionPermissions = array();

	/**
	 * The action permissions defaults
	 *
	 * @var array
	 */
	protected $actionPermissionsDefaults = array(
		'create' => true,
		'delete' => true,
		'update' => true,
		'view' => true,
	);

	/**
	 * Create a new action Factory instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 */
	public function __construct(Validator $validator, ConfigInterface $config)
	{
		$this->config = $config;
		$this->validator = $validator;
	}

	/**
	 * Takes the model and an info array of options for the specific action
	 *
	 * @param string		$name		//the key name for this action
	 * @param array			$options
	 *
	 * @return Frozennode\Administrator\Actions\Action
	 */
	public function make($name, $options)
	{
		//check the permission on this item
		$options = $this->parseDefaults($name, $options);

		//now we can instantiate the object
		return $this->getActionObject($options);
	}

	/**
	 * Sets up the default values for the $options array
	 *
	 * @param string		$name		//the key name for this action
	 * @param array			$options
	 *
	 * @return array
	 */
	public function parseDefaults($name, $options)
	{
		$model = $this->config->getDataModel();

		//if the name is not a string or the options is not an array at this point, throw an error because we can't do anything with it
		if (!is_string($name) || !is_array($options))
		{
			throw new \InvalidArgumentException("A custom action in your  " . $this->config->getOption('name') . " configuration file is invalid");
		}

		//set the action name
		$options['action_name'] = $name;

		//set the permission
		$options['has_permission'] = is_callable($this->validator->arrayGet($options, 'permission', false)) ? $options['permission']($model) : true;

		//check if the messages array exists
		$options['messages'] = $this->validator->arrayGet($options, 'messages', array());
		$options['messages'] = is_array($options['messages']) ? $options['messages'] : array();

		return $options;
	}

	/**
	 * Gets an Action object
	 *
	 * @param array		$options
	 *
	 * @return Frozennode\Administrator\Actions\Action
	 */
	public function getActionObject(array $options)
	{
		return new Action($this->validator, $this->config, $options);
	}

	/**
	 * Gets an action by name
	 *
	 * @param string	$name
	 *
	 * @return mixed
	 */
	public function getByName($name)
	{
		//loop over the actions to find our culprit
		foreach ($this->getActions() as $action)
		{
			if ($action->name === $name)
			{
				return $action;
			}
		}

		return false;
	}

	/**
	 * Gets all actions
	 *
	 * @param bool	$override
	 *
	 * @return array of Action objects
	 */
	public function getActions($override = false)
	{
		//make sure we only run this once and then return the cached version
		if (!sizeof($this->actions) || $override)
		{
			$this->actions = array();

			//loop over the actions to build the list
			foreach ($this->config->getOption('actions') as $name => $options)
			{
				$this->actions[] = $this->make($name, $options);
			}
		}

		return $this->actions;
	}

	/**
	 * Gets all action permissions
	 *
	 * @param bool	$override
	 *
	 * @return array of Action objects
	 */
	public function getActionPermissions($override = false)
	{
		//make sure we only run this once and then return the cached version
		if (!sizeof($this->actionPermissions) || $override)
		{
			$this->actionPermissions = array();
			$model = $this->config->getDataModel();
			$options = $this->config->getOption('action_permissions');
			$defaults = $this->actionPermissionsDefaults;

			//merge the user-supplied action permissions into the defaults
			$permissions = array_merge($defaults, $options);

			//loop over the actions to build the list
			foreach ($permissions as $action => $callback)
			{
				if (is_callable($callback))
				{
					$this->actionPermissions[$action] = (bool) $callback();
				}
				else
				{
					$this->actionPermissions[$action] = (bool) $callback;
				}
			}
		}

		return $this->actionPermissions;
	}
}