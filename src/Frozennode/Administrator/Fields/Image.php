<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;
use Frozennode\Administrator\Includes\Multup;
use Illuminate\Support\Facades\URL;

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
	 * Create a new Image instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		parent::__construct($validator, $config, $db, $options);

		$this->sizes = $this->validator->arrayGet($options, 'sizes', $this->sizes);
	}

	/**
	 * This static function is used to perform the actual upload and resizing using the Multup class
	 *
	 * @return array
	 */
	public function doUpload()
	{
		//use the multup library to perform the upload
		$result = Multup::open('file', 'image|max:' . $this->sizeLimit * 1000, $this->location, $this->naming)
			->sizes($this->sizes)
			->set_length($this->length)
			->upload();

		return $result[0];
	}
}