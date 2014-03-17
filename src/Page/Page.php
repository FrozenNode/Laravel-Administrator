<?php namespace Frozennode\Administrator\Page;

abstract class Page {

	use OptionableTrait;

	/**
	 * Create a new Config instance
	 *
	 * @param array 	$options
	 */
	public function __construct(array $options)
	{
		$this->options = $options;
	}

	/**
	 * Page type getter
	 *
	 * @return  string
	 */
	abstract public function getType();

	/**
	 * Builds the necessary fields on the object
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		//check the permission
		$options['permission'] = isset($options['permission']) ? $options['permission']() : true;

		return $options;
	}

}