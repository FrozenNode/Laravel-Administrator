<?php namespace Frozennode\Administrator\Config;

use Frozennode\Administrator\Traits\OptionableTrait;

abstract class Config {

	use OptionableTrait;

	/**
	 * Create a new Config instance
	 *
	 * @param array 	$options
	 */
	public function __construct(array $options)
	{
		$this->suppliedOptions = $options;
	}

	/**
	 * Builds the necessary fields on the object
	 *
	 * @return void
	 */
	public function buildOptions()
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
	 * Validates the supplied data against the options rules
	 *
	 * @param array		$data
	 * @param array		$rules
	 *
	 * @param mixed
	 */
	public function validateData(array $data, array $rules)
	{
		if ($rules)
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