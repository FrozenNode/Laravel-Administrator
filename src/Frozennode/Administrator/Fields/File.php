<?php
namespace Frozennode\Administrator\Fields;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Frozennode\Administrator\Includes\Multup;

class File extends Field {

	/**
	 * The naming mechanism for the file (can be either 'keep' or 'random').
	 *
	 * @var string
	 */
	public $naming = 'random';

	/**
	 * Length of file name if naming is set to random
	 *
	 * @var int
	 */
	public $length = 32;

	/**
	 * The directory location used to store the file
	 *
	 * @var string
	 */
	public $location;

	/**
	 * The upload url for this field
	 *
	 * @var string
	 */
	public $uploadUrl;

	/**
	 * The file size limit in MB
	 *
	 * @var int
	 */
	public $sizeLimit = 2;

	/**
	 * The mime types that this field should be limited to separated by commas
	 *
	 * @var false | string
	 */
	public $mimes = false;

	/**
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param ModelConfig 	$config
	 */
	public function __construct($field, $info, $config)
	{
		parent::__construct($field, $info, $config);
		$isSettings = is_a($config, 'Frozennode\\Administrator\\SettingsConfig');

		$this->mimes = array_get($info, 'mimes', $this->mimes);
		$this->naming = array_get($info, 'naming', $this->naming);
		$this->length = array_get($info, 'length', $this->length);
		$this->location = array_get($info, 'location');
		$this->sizeLimit = (int) array_get($info, 'size_limit', $this->sizeLimit);

		if ($isSettings)
		{
			$this->uploadUrl = URL::route('admin_settings_file_upload', array($config->name, $this->field));
		}
		else
		{
			$this->uploadUrl = URL::route('admin_file_upload', array($config->name, $this->field));
		}

		//make sure the naming is one of the two accepted values
		$this->naming = in_array($this->naming, array('keep', 'random')) ? $this->naming : 'random';

		// Satisfy params for Multup, for keep we return false so we don't random filename
		$this->naming = ($this->naming == 'keep') ? false : true;
	}

	/**
	 * This static function is used to perform the actual upload and resizing using the Multup class
	 *
	 * @return array
	 */
	public function doUpload()
	{
		$mimes = $this->mimes ? '|mimes:' . $this->mimes : '';

		//use the multup library to perform the upload
		$result = Multup::open('file', 'max:' . $this->sizeLimit * 1000 . $mimes, $this->location, $this->naming)
			->set_length($this->length)
			->upload();

		return $result[0];
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['location'] = $this->location;
		$arr['size_limit'] = $this->sizeLimit;
		$arr['upload_url'] = $this->uploadUrl;

		return $arr;
	}
}