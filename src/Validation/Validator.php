<?php
namespace Frozennode\Administrator\Validation;

class Validator extends \Illuminate\Validation\Validator {

	protected $overrideCustomMessages = [
		'string' => "The :attribute option must be a string",
		'directory' => "The :attribute option must be a valid directory",
		'array' => "The :attribute option must be an array",
		'array_with' => "The :attribute array is missing some required values",
		'not_empty' => "The :attribute option must not be empty",
		'callable' => "The :attribute option must be a function",
		'eloquent' => "The :attribute option must be the string name of a valid Eloquent model",
	];

	/**
	 * The URL instance
	 *
	 * @var \Illuminate\Routing\UrlGenerator
	 */
	protected $url;

	/**
	 * Injects the URL class instance
	 *
	 * @param \Illuminate\Routing\UrlGenerator $url
	 *
	 * @return void
	 */
	public function setUrlInstance(\Illuminate\Routing\UrlGenerator $url)
	{
		$this->url = $url;
	}

	/**
	 * Gets the URL class instance
	 *
	 * @return \Illuminate\Routing\UrlGenerator
	 */
	public function getUrlInstance()
	{
		return $this->url;
	}

	/**
	 * Overrides the rules and data
	 *
	 * @param array		$data
	 * @param array		$rules
	 *
	 * @return void
	 */
	public function override($data, $rules)
	{
		$this->setData($data);
		$this->setRules($rules);
		$this->setCustomMessages($this->overrideCustomMessages);
	}

	/**
	 * Sets the rules
	 *
	 * @param array		$rules
	 */
	public function setRules(array $rules)
	{
		$this->rules = $this->explodeRules($rules);
	}

	/**
	 * Validates that an item is a string
	 */
	public function validateString($attribute, $value, $parameters)
	{
		return is_string($value);
	}

	/**
	 * Validates that an item is a directory
	 */
	public function validateDirectory($attribute, $value, $parameters)
	{
		return is_dir($value);
	}

	/**
	 * Validates that an item is an array
	 */
	public function validateArray($attribute, $value)
	{
		return is_array($value);
	}

	/**
	 * Validates that an item is an array
	 */
	public function validateArrayWithAllOrNone($attribute, $value, $parameters)
	{
		$missing = 0;

		foreach ($parameters as $key)
		{
			if (!isset($value[$key]))
			{
				$missing++;
			}
		}

		return $missing === count($parameters) || $missing === 0;
	}

	/**
	 * Validates that an item is not empty
	 */
	public function validateNotEmpty($attribute, $value, $parameters)
	{
		return !empty($value);
	}

	/**
	 * Validates that an item is callable
	 */
	public function validateCallable($attribute, $value, $parameters)
	{
		return is_callable($value);
	}

	/**
	 * Validates that an item is either a string or callable
	 */
	public function validateStringOrCallable($attribute, $value, $parameters)
	{
		return is_string($value) || is_callable($value);
	}

	/**
	 * Validates that an item is an Eloquent model
	 */
	public function validateEloquent($attribute, $value, $parameters)
	{
		return class_exists($value) && is_a(new $value, 'Illuminate\Database\Eloquent\Model');
	}

}
