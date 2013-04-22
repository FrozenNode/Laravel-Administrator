<?php
namespace Admin\Libraries\Fields;

use Admin\Libraries\Includes\Multup;
use \URL;

class Image extends File {

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
	}

	/**
	 * This static function is used to perform the actual upload and resizing using the Multup class
	 *
	 * @return array
	 */
	public function doUpload()
	{
		//use the multup library to perform the upload
		$result = Multup::open('file', 'image|max:' . $this->sizeLimit * 1000 . '|mimes:jpg,gif,png', $this->location, $this->naming)
			->sizes($this->sizes)
			->set_length($this->length)
			->upload();

		return $result[0];
	}
}