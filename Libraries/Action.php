<?php
namespace Admin\Libraries;

class Action {

	/**
	 * The name of the action
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The title of the action button
	 *
	 * @var string
	 */
	public $title = 'Custom Action';

	/**
	 * If this is true, this user has permission to access this action
	 *
	 * @var bool
	 */
	public $hasPermission;

	/**
	 * The action messages for this button
	 *
	 * @var array
	 */
	public $messages = array(
		'active' => 'Just a moment...',
		'success' => 'Success!',
		'error' => 'There was an error performing this action',
	);

	/**
	 * The function to run for this action
	 *
	 * @var closure
	 */
	public $action;

	/**
	 * Constructor function
	 *
	 * @param string	$field
	 * @param string	$direction
	 */
	public function __construct($name, $info)
	{
		$this->name = $name;
		$this->title = array_get($info, 'title', $this->title);
		$this->hasPermission = $info['hasPermission'];

		//run through the messages
		$this->messages['active'] = array_get($info['messages'], 'active', __('administrator::administrator.active'));
		$this->messages['success'] = array_get($info['messages'], 'success', __('administrator::administrator.success'));
		$this->messages['error'] = array_get($info['messages'], 'error', __('administrator::administrator.error'));

		//set up the action
		$this->action = $info['action'];
	}


	/**
	 * Takes the model and an info array of options for the specific action
	 *
	 * @param string		$name		//the key name for this action
	 * @param array			$info 		//the array info provided by the user
	 *
	 * @return false|Action object
	 */
	public static function create($name, $info)
	{
		//check the permission on this item
		$info['hasPermission'] = is_callable(array_get($info, 'permission', false)) ? $info['permission']() : true;

		//check if the messages array exists
		$info['messages'] = array_get($info, 'messages', array());
		$info['messages'] = is_array($info['messages']) ? $info['messages'] : array();

		//set up the action as a NO-OP if it doesn't exist
		$info['action'] = is_callable(array_get($info, 'action', false)) ? $info['action'] : function() {};

		//now we can instantiate the object
		return new static($name, $info);
	}

	/**
	 * Gets an action by name
	 *
	 * @param ModelConfig	$config
	 * @param string		$name
	 *
	 * @return false|Action object
	 */
	public static function getByName($config, $name)
	{
		//check if the model has actions
		if (!$config->actions || !is_array($config->actions))
		{
			return false;
		}

		//loop over the actions to find our culprit
		foreach ($config->actions as $action)
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
	 * @param ModelConfig	$config
	 *
	 * @return false|array of Action objects and arrays
	 */
	public static function getActions($config)
	{
		//check if the model has actions
		if (!$config->actions || !is_array($config->actions))
		{
			return false;
		}

		//we'll be returning both the objects and the arrays
		$validActions = array();

		//loop over the actions to build the list
		foreach ($config->actions as $name => $info)
		{
			if ($action = static::create($name, $info))
			{
				$validActions[] = $action;
			}
		}

		return sizeof($validActions) ? $validActions : false;
	}

	/**
	 * Performs the callback of the action and returns its result
	 *
	 * @param Eloquent	$model
	 *
	 * @return array
	 */
	public function perform($model)
	{
		return call_user_func($this->action, $model);
	}

	/**
	 * Turn sort options into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'name' => $this->name,
			'title' => $this->title,
			'hasPermission' => $this->hasPermission,
			'messages' => $this->messages,
		);
	}
}