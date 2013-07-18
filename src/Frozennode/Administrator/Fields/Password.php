<?php
namespace Frozennode\Administrator\Fields;

class Password extends Text {

	/**
	 * The specific defaults for the image class
	 *
	 * @var array
	 */
	protected $passwordDefaults = array(
		'setter' => true,
	);

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		$defaults = parent::getDefaults();

		return array_merge($defaults, $this->passwordDefaults);
	}
}