<?php namespace Frozennode\Administrator\Fields\Types;

use Frozennode\Administrator\Fields\Field;
use Frozennode\Administrator\Includes\Multup;

class File extends Field {

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaultOptions = [
		'naming' => 'random',
		'length' => 32,
		'mimes' => false,
		'size_limit' => 2,
	];

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = [
		'location' => 'required|string|directory',
		'naming' => 'in:keep,random',
		'length' => 'integer|min:0',
		'mimes' => 'string',
	];

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'file';
	}

	/**
	 * Builds a few basic options
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		$options = parent::buildOptions($options);

		//set the upload url depending on the type of config this is
		$route = $this->config->getType() === 'settings' ? 'admin_settings_file_upload' : 'admin_file_upload';

		//set the upload url to the proper route
		$options['upload_url'] = route($route, [$this->config->getOption('name'), $options['field_name']]);

		return $options;
	}

	/**
	 * This static function is used to perform the actual upload and resizing using the Multup class
	 *
	 * @return array
	 */
	public function doUpload()
	{
		$mimes = $this->getOption('mimes') ? '|mimes:' . $this->getOption('mimes') : '';

		//use the multup library to perform the upload
		$result = Multup::open('file', 'max:' . $this->getOption('size_limit') * 1000 . $mimes, $this->getOption('location'),
									$this->getOption('naming') === 'random')
			->set_length($this->getOption('length'))
			->upload();

		return $result[0];
	}
}