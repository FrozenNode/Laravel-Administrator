<?php
namespace Admin\Libraries\Fields;

use Admin\Libraries\ModelHelper;

class Image extends Field {

	/**
	 * The naming mechanism for the image (can be either 'keep' or 'random').
	 *
	 * @var string
	 */
	public $naming = 'random';

	/**
	 * The directory location used to store the original
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
	 * The display url for this field
	 *
	 * @var string
	 */
	public $displayUrl;

	/**
	 * The file size limit in MB
	 *
	 * @var int
	 */
	public $sizeLimit = 2;

	/**
	 * If provided, the user can specify thumbnail sizes and locations. Example:
	 * array(
	 *		array(65, 57, 'crop', 'public/uimg/listings/thumbs/small/', 100),
	 *		array(220, 138, 'crop', 'public/uimg/listings/thumbs/medium/', 100),
	 *		array(383, 276, 'crop', 'public/uimg/listings/thumbs/full/', 100)
	 *	)
	 *
	 * @var array
	 */
	public $sizes = array();

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

		$this->sizes = array_get($info, 'sizes', $this->sizes);
		$this->naming = array_get($info, 'naming', $this->naming);
		$this->location = array_get($info, 'location');
		$this->sizeLimit = (int) array_get($info, 'size_limit', $this->sizeLimit);
		$this->uploadUrl = \URL::to_route('admin_image_upload', array($config->name, $this->field));

		$replace = path('public');


		if (strpos($this->location, $replace) === 0)
		{
			$this->displayUrl = \URL::to('/' . substr_replace($this->location, '', 0, strlen($replace)));
		}

		//make sure the naming is one of the two accepted values
		$this->naming = in_array($this->naming, array('keep', 'random')) ? $this->naming : 'random';
	}

	/**
	 * This static function is used to perform the actual upload and resizing using the Multup class
	 *
	 * @return array
	 */
	public function doUpload()
	{
		//use the multup library to perform the upload
		$result = \Admin\Libraries\Includes\Multup::open('file', 'image|max:' . $this->sizeLimit * 1000 . '|mimes:jpg,gif,png', $this->location, true)
			->sizes($this->sizes)
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
		$arr['display_url'] = $this->displayUrl;

		return $arr;
	}
}
