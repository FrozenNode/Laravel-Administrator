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

		//set the upload url to the proper route
		if ($this->config->getType() === 'settings') 
		{
			$this->suppliedOptions['upload_url'] = admin_url( 'settings/' . $this->config->getOption('name') . '/' . $this->suppliedOptions['field_name']. '/file_upload');
		}		
		else 
		{
			$this->suppliedOptions['upload_url'] = admin_url($this->config->getOption('name') . '/' . $this->suppliedOptions['field_name']. '/file_upload');
		}
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