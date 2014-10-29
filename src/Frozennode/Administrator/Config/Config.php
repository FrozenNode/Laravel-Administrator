<?php
namespace Frozennode\Administrator\Config;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Validation\Validator as CustomValidator;

abstract class Config {

	/**
	 * The validator instance
	 *
	 * @var \Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The site's normal validator instance
	 *
	 * @var \Illuminate\Validation\Validator
	 */
	protected $customValidator;

	/**
	 * The user supplied options array
	 *
	 * @var array
	 */
	protected $suppliedOptions = array();

	/**
	 * The original configuration options that were supplied
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The defaults property
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * The rules property
	 *
	 * @var array
	 */
	protected $rules = array();

	/**
	 * Create a new model Config instance
	 *
	 * @param \Frozennode\Administrator\Validator 	$validator
	 * @param \Illuminate\Validation\Validator	 	$custom_validator
	 * @param array 								$options
	 */
	public function __construct(Validator $validator, CustomValidator $custom_validator, array $options)
	{
		$this->validator = $validator;
		$this->customValidator = $custom_validator;
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
			throw new \InvalidArgumentException('There are problems with your ' . $this->suppliedOptions['name'] . ' config: ' .
						implode('. ', $this->validator->messages()->all()));
		}
	}

	/**
	 * Builds the necessary fields on the object
	 *
	 * @return void
	 */
	public function build()
	{
		$options = $this->suppliedOptions;

		//check the permission
		$options['permission'] = isset($options['permission']) ? $options['permission']() : true;

		$this->suppliedOptions = $options;
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
	 * Gets a config option
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
			throw new \InvalidArgumentException("An invalid option was searched for in the '" . $options['name'] . "' config");
		}

		return $options[$key];
	}

	/**
	 * Sets the user options
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function setOptions(array $options)
	{
		//unset the current options
		$this->options = array();

		//override the supplied options
		$this->suppliedOptions = $options;
	}

	/**
	 * Validates the supplied data against the options rules
	 *
	 * @param array		$data
	 * @param array		$rules
	 * @param array		$messages
	 *
	 * @param mixed
	 */
	public function validateData(array $data, array $rules, array $messages)
	{
		if ($rules)
		{
			$this->customValidator->setData($data);
			$this->customValidator->setRules($rules);
			$this->customValidator->setCustomMessages($messages);

			//if the validator fails, kick back the errors
			if ($this->customValidator->fails())
			{
				return implode('. ', $this->customValidator->messages()->all());
			}
		}

		return true;
	}
}