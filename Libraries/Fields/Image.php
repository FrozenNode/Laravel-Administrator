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
	 * The relative location used to store the original
	 * within public directory
	 *
	 * @var string
	 */
	public $location;

	/**
	 * The url for getting preview
	 * (when using $location_path).
	 *
	 * @var string
	 */
	public $location_upload_url;

	/**
	 * The directory location used to store the original
	 * (when not using $location)
	 *
	 * @var string
	 */
	public $location_upload_path;

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
		$this->location_upload_path = array_get($info, 'location_upload_path');
		$this->location_upload_url = array_get($info, 'location_upload_url');
		$this->sizeLimit = (int) array_get($info, 'size_limit', $this->sizeLimit);
		$this->uploadUrl = \URL::to_route('admin_image_upload', array($config->name, $this->field));

		//Deciding what the displayUrl is
		if($this->location_upload_url && $this->location_upload_path)
		{
			$this->displayUrl = $this->location_upload_url;
			
		} else {
			// Bit of legacy code - user can have either:
			// path('public').'uploads/somepath/' or
			// 'public/uploads/somepath
			$public_path = path('public');
			$pattern = "#^(public/|$public_path)(.*)#";
			if(preg_match($pattern, $this->location)) {
				$this->displayUrl = \URL::to(preg_replace($pattern, '$2', $this->location));
			} else {
				$this->displayUrl = \URL::to($this->location);
			}
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
		$location = '';
		// Since there are two methods of specyfing location, deciding where to upload file
		if($this->location_upload_url && $this->location_upload_path) {
			$location = $this->location_upload_path;
		} else {
			$location = path('public') . $this->location;
		}

		//use the multup library to perform the upload
		$result = \Admin\Libraries\Includes\Multup::open('file', 'image|max:' . $this->sizeLimit * 1000 . '|mimes:jpg,gif,png', $location, true)
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
		$arr['location_upload_path'] = $this->location_upload_path;
		$arr['location_upload_url'] = $this->location_upload_url;
		$arr['size_limit'] = $this->sizeLimit;
		$arr['upload_url'] = $this->uploadUrl;
		$arr['display_url'] = $this->displayUrl;

		return $arr;
	}
}
