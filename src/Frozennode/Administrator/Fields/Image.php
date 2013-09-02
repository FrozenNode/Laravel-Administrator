<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Includes\Multup;

class Image extends File {

	/**
	 * The specific defaults for the image class
	 *
	 * @var array
	 */
	protected $imageDefaults = array(
		'sizes' => array(),
	);

	/**
	 * The specific rules for the image class
	 *
	 * @var array
	 */
	protected $imageRules = array(
		'sizes' => 'array',
	);

	/**
	 * This static function is used to perform the actual upload and resizing using the Multup class
	 *
	 * @return array
	 */
	public function doUpload()
	{
		//use the multup library to perform the upload
		$result = Multup::open('file', 'image|max:' . $this->getOption('size_limit') * 1000, $this->getOption('location'),
									$this->getOption('naming') === 'random')
			->sizes($this->getOption('sizes'))
			->set_length($this->getOption('length'))
			->upload();

		return $result[0];
	}

	/**
	 * Gets all rules
	 *
	 * @return array
	 */
	public function getRules()
	{
		$rules = parent::getRules();

		return array_merge($rules, $this->imageRules);
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		$defaults = parent::getDefaults();

		return array_merge($defaults, $this->imageDefaults);
	}
}