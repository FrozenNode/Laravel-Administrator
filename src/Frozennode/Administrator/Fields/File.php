<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Support\Facades\URL;
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
	 * Create a new File instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		parent::__construct($validator, $config, $db, $options);

		$this->mimes = $this->validator->arrayGet($options, 'mimes', $this->mimes);
		$this->naming = $this->validator->arrayGet($options, 'naming', $this->naming);
		$this->length = $this->validator->arrayGet($options, 'length', $this->length);
		$this->location = $this->validator->arrayGet($options, 'location');
		$this->sizeLimit = (int) $this->validator->arrayGet($options, 'size_limit', $this->sizeLimit);

		//make sure the naming is one of the two accepted values
		$this->naming = in_array($this->naming, array('keep', 'random')) ? $this->naming : 'random';

		// Satisfy params for Multup, for keep we return false so we don't random filename
		$this->naming = ($this->naming == 'keep') ? false : true;

		//set the upload url depending on the type of config this is
		$this->setUploadUrl();
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

	/**
	 * Gets the upload URL depending on the type of page this is
	 */
	public function setUploadUrl()
	{
		if ($this->config->getType() === 'settings')
		{
			$this->uploadUrl = URL::route('admin_settings_file_upload', array($this->config->getOption('name'), $this->field));
		}
		else
		{
			$this->uploadUrl = URL::route('admin_file_upload', array($this->config->getOption('name'), $this->field));
		}
	}
}