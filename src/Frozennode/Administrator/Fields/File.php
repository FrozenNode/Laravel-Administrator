<?php
namespace Frozennode\Administrator\Fields;

use Frozennode\Administrator\Includes\Multup;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\File as LaravelFile;

class File extends Field {

	const NAMING_KEEP           = 'keep';
	const RANDOM_DEFAULT_LENGTH = 32;

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
		'display_raw_value' => false,
	);

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = array(
		'location' => 'required|string|directory',
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
		$model = $this->config->getDataModel();

		$uploadUrl = $url->route(
			$route,
			array(
				'model' => $this->config->getOption('name'),
				'field' => $this->suppliedOptions['field_name'],
				'id'    => $model ? $model->{$model->getKeyName()} : null,
			)
		);

        $this->suppliedOptions['upload_url'] = preg_replace('$([^:])(//)$', '\1/', $uploadUrl);
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
									$this->getFilename())
			->upload();

		return $result[0];
	}

	/**
	 * @return UploadedFile
	 */
	protected function getFile()
	{
		return Input::file('file');
	}

	/**
	 * @return string
	 */
	protected function getFileExtension()
	{
        $file = $this->getFile();

		return LaravelFile::extension($file->getClientOriginalName());
	}

	/**
	 * @return null
	 */
	protected function getFilename()
	{
		$naming = $this->getOption('naming');

		if (self::NAMING_KEEP === $naming) {
			return $this->getFile()->getClientOriginalName();
		}

		$filename = $this->getCustomFilename();

		if ($filename) {
			return $filename;
		}

		return $this->getRandomFileName();
	}

	/**
	 * @return string|null
	 */
	protected function getCustomFilename()
	{
		$naming = $this->getOption('naming');

		if (!is_callable($naming)) {
			return null;
		}

		return call_user_func($naming, $this->config->getDataModel(), $this->getFile());
	}

	/**
	 * @return string
	 */
	protected function getRandomFilename()
	{
		$length = self::RANDOM_DEFAULT_LENGTH;

		if (isset($this->suppliedOptions['length'])) {
			$length = $this->suppliedOptions['length'];
		}
		return Str::random($length) . '.' . $this->getFileExtension();
	}
}
