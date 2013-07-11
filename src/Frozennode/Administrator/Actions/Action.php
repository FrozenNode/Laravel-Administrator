<?php
namespace Frozennode\Administrator\Actions;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;

class Action {

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
	 * If this is a string, flash a confirmation message
	 *
	 * @var string
	 */
	public $confirmation = false;

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
	 * Create a new action Factory instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, array $options)
	{
		$this->config = $config;
		$this->validator = $validator;
		$this->name = $options['action_name'];
		$this->title = $this->validator->arrayGet($options, 'title', $this->title);
		$this->hasPermission = $options['has_permission'];

		//check if a confirmation was supplied
		$confirmation = $this->validator->arrayGet($options, 'confirmation');

		if (is_string($confirmation))
		{
			$this->confirmation = $confirmation;
		}
		else if (is_callable($confirmation))
		{
			$this->confirmation = $confirmation($model);
		}

		//run through the messages
		$this->messages['active'] = $this->validator->arrayGet($options['messages'], 'active', trans('administrator::administrator.active'));
		$this->messages['success'] = $this->validator->arrayGet($options['messages'], 'success', trans('administrator::administrator.success'));
		$this->messages['error'] = $this->validator->arrayGet($options['messages'], 'error', trans('administrator::administrator.error'));

		//set up the action
		$this->action = $this->validator->arrayGet($options, 'action', function() {});
	}

	/**
	 * Performs the callback of the action and returns its result
	 *
	 * @param mixed		$data
	 *
	 * @return array
	 */
	public function perform(&$data)
	{
		$action = $this->action;
		return $action($data);
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
			'confirmation' => $this->confirmation,
		);
	}
}