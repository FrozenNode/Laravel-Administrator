<?php
namespace Frozennode\Administrator\Actions;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;

class Action {

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
	 * The user supplied options array
	 *
	 * @var array
	 */
	protected $suppliedOptions = array();

	/**
	 * The options array
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $defaults = array(
		'title' => 'Custom Action',
		'has_permission' => true,
		'confirmation' => false,
		'messages' => array(
			'active' => 'Just a moment...',
			'success' => 'Success!',
			'error' => 'There was an error performing this action',
		),
	);

	/**
	 * The base rules that all fields need to pass
	 *
	 * @var array
	 */
	protected $rules = array(
		'title' => 'string',
		'messages' => 'array|array_with_all_or_none:active,success,error',
		'action' => 'required|callable',
	);

	/**
	 * Create a new action Factory instance
	 *
	 * @param \Frozennode\Administrator\Validator 				$validator
	 * @param \Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, array $options)
	{
		$this->config = $config;
		$this->validator = $validator;
		$this->suppliedOptions = $options;
	}

	/**
	 * Validates the supplied options
	 *
	 * @return void
	 */
	public function validateOptions()
	{
		//override the config
		$this->validator->override($this->suppliedOptions, $this->rules);

		//if the validator failed, throw an exception
		if ($this->validator->fails())
		{
			throw new \InvalidArgumentException("There are problems with your '" . $this->suppliedOptions['action_name'] . "' action in the " .
									$this->config->getOption('name') . " model: " .	implode('. ', $this->validator->messages()->all()));
		}
	}

	/**
	 * Builds the necessary fields on the object
	 *
	 * @return void
	 */
	public function build()
	{
		$model = $this->config->getDataModel();
		$options = $this->suppliedOptions;

		//check if a confirmation was supplied
		$confirmation = $this->validator->arrayGet($options, 'confirmation');

		//if it's a string, simply set it
		if (is_string($confirmation))
		{
			$options['confirmation'] = $confirmation;
		}
		//if it's callable pass it the current model and run it
		else if (is_callable($confirmation))
		{
			$options['confirmation'] = $confirmation($model);
		}

		$this->suppliedOptions = $options;
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
		$action = $this->getOption('action');
		return $action($data);
	}

	/**
	 * Gets all user options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		//make sure the supplied options have been merged with the defaults
		if (empty($this->options))
		{
			//validate the options and build them
			$this->validateOptions();
			$this->build();
			$this->options = array_merge($this->defaults, $this->suppliedOptions);
		}

		return $this->options;
	}

	/**
	 * Gets a field's option
	 *
	 * @param string 	$key
	 *
	 * @return mixed
	 */
	public function getOption($key)
	{
		$options = $this->getOptions();

		if (!array_key_exists($key, $options))
		{
			throw new \InvalidArgumentException("An invalid option was searched for in the '" . $options['action_name'] . "' action");
		}

		return $options[$key];
	}
}