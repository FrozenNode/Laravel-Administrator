<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Includes\Multup;

class File extends Field {

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array(
		'naming' => 'random',
		'length' => 32,
		'mimes' => false,
		'size_limit' => 2,
	);

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = array(
		'location' => 'required|string|directory',
		'naming' => 'in:keep,random',
		'length' => 'integer|min:0',
		'mimes' => 'string',
	);

	/**
	 * Builds a few basic options
	 */
	public function build()
	{
		parent::build();

		//set the upload url depending on the type of config this is
		$url = $this->validator->getUrlInstance();
		$route = $this->config->getType() === 'settings' ? 'admin_settings_file_upload' : 'admin_file_upload';

		//set the upload url to the proper route
		$this->suppliedOptions['upload_url'] = $url->route($route, array($this->config->getOption('name'), $this->suppliedOptions['field_name']));
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