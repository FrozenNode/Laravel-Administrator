<?php
namespace Frozennode\Administrator\Fields;

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
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaultOptions()
	{
		return array_merge(parent::getDefaultOptions(), $this->passwordDefaultOptions);
	}
}