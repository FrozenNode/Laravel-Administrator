<?php
namespace Frozennode\Administrator\Config;

use Frozennode\Administrator\Config\ConfigInterface;
use Frozennode\Administrator\Validator;

abstract class Config {

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The original configuration options that were supplied
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Determines whether or not a user can access this model
	 *
	 * @var bool
	 */
	protected $permission = true;

	/**
	 * The rules array
	 *
	 * @var array
	 */
	protected $rules = array(
		'title' => 'required|string',
		'edit_fields' => 'required|array|not_empty',
		'permission' => 'callable',
		'action_permissions' => 'array',
		'actions' => 'array',
		'sort' => 'array',
		'form_width' => 'integer',
		'link' => 'callable',
		'rules' => 'array',
	);


	/**
	 * Create a new model Config instance
	 *
	 * @param Frozennode\Administrator\Validator 	$validator
	 * @param array 								$config
	 */
	public function __construct(Validator $validator, array $config)
	{
		//set the config, and then validate it
		$this->validator = $validator;
		$validator->override($config, $this->rules);

		//if the validator failed, throw an exception
		if ($validator->fails())
		{
			throw new \InvalidArgumentException('There are problems with your ' . $config['name'] . ' config: ' . implode('. ', $validator->messages()->all()));
		}

		//check the permission
		$this->permission = isset($config['permission']) ? $config['permission']() : $this->permission;

		//fill up the instance with the user-supplied options
		$this->options = array_merge($this->defaults, $config);
	}

	/**
	 * Permission getter
	 *
	 * @return  bool
	 */
	public function getPermission()
	{
		return $this->permission;
	}

	/**
	 * Config type getter
	 *
	 * @return  string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Gets a config option
	 *
	 * @param string 	$key
	 *
	 * @return mixed
	 */
	public function getOption($key)
	{
		if (!array_key_exists($key, $this->options))
		{
			throw new \InvalidArgumentException("An invalid option was searched for in '" . $this->getOption('name') . "'");
		}

		return $this->options[$key];
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
}