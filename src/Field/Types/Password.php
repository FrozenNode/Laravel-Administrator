<?php namespace Frozennode\Administrator\Field\Types;

class Password extends Text {

	/**
	 * The specific default options for the password class
	 *
	 * @var array
	 */
	protected $passwordDefaultOptions = [
		'setter' => true,
	];

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'password';
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaultOptions()
	{
		return array_merge(parent::getDefaultOptions(), $this->passwordDefaultOptions);
	}
}