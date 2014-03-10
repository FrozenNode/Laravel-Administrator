<?php namespace Frozennode\Administrator\Traits;

use Frozennode\Administrator\Validation\Validator;

trait OptionableTrait {

	/**
	 * The Validator instance
	 *
	 * @var \Frozennode\Administrator\Validator
	 */
	protected $optionsValidator;

	/**
	 * The original configuration options that were supplied
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * The user-supplied options array
	 *
	 * @var array
	 */
	protected $formattedOptions = [];

	/**
	 * Sets the user options
	 *
	 * @param array		$options
	 *
	 * @return void
	 */
	public function setOptions(array $options)
	{
		//unset the current options
		$this->formattedOptions = [];

		//override the supplied options
		$this->options = $options;
	}

	/**
	 * Sets the user options
	 *
	 * @param \IlluminateValidator		$validator
	 *
	 * @return void
	 */
	public function setOptionsValidator(Validator $validator)
	{
		//unset the current options
		$this->validator = $validator;
	}

	/**
	 * Validates the supplied options
	 *
	 * @return void
	 */
	public function validateOptions()
	{
		//override the config
		$this->validator->override($this->options, $this->rules);

		//if the validator failed, throw an exception
		if ($this->validator->fails())
		{
			throw new \InvalidArgumentException('There are problems with your ' . $this->options['name'] . ' config: ' .
						implode('. ', $this->validator->messages()->all()));
		}
	}

	/**
	 * Gets all user options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		//make sure the supplied options have been merged with the defaults
		if (empty($this->formattedOptions))
		{
			//validate the options and build them
			$this->validateOptions();

			//if the class has a buildOptions method, run it
			if (method_exists($this, 'buildOptions'))
				$this->options = $this->buildOptions($this->options);

			//set the options array to the now-formatted original options merged into the default options
			$this->formattedOptions = array_merge($this->defaultOptions, $this->options);
		}

		return $this->formattedOptions;
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

}