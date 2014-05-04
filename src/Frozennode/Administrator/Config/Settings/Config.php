<?php
namespace Frozennode\Administrator\Config\Settings;

use Frozennode\Administrator\Config\Config as ConfigBase;
use Frozennode\Administrator\Config\ConfigInterface;

class Config extends ConfigBase implements ConfigInterface {

	/**
	 * The config type
	 *
	 * @var string
	 */
	protected $type = 'settings';

	/**
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $defaults = array(
		'permission' => true,
		'before_save' => null,
		'actions' => array(),
		'rules' => array(),
		'storage_path' => null,
	);

	/**
	 * An array with the settings data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The rules array
	 *
	 * @var array
	 */
	protected $rules = array(
		'title' => 'required|string',
		'edit_fields' => 'required|array|not_empty',
		'permission' => 'callable',
		'before_save' => 'callable',
		'actions' => 'array',
		'rules' => 'array',
		'storage_path' => 'directory',
	);

	/**
	 * Fetches the data model for a config
	 *
	 * @return  array
	 */
	public function getDataModel()
	{
		return $this->data;
	}

	/**
	 * Sets the data model for a config
	 *
	 * @param array		$data
	 *
	 * @return  void
	 */
	public function setDataModel($data)
	{
		$this->data = $data;
	}

	/**
	 * Gets the storage directory path
	 */
	public function getStoragePath()
	{
		$path = $this->getOption('storage_path');
		$path = $path ? $path : storage_path() . '/administrator_settings/';
		return rtrim($path, '/') . '/';
	}

	/**
	 * Fetches the data for this settings config and stores it in the data property
	 *
	 * @param array		$fields
	 *
	 * @return void
	 */
	public function fetchData(array $fields)
	{
		//set up the blank data
		$data = array();

		foreach ($fields as $name => $field)
		{
			$data[$name] = null;
		}

		//populate the data from the file
		$this->setDataModel($this->populateData($data));
	}

	/**
	 * Populates the data array if it can find the settings file
	 *
	 * @param array		$data
	 *
	 * @return array
	 */
	public function populateData(array $data)
	{
		//attempt to make the storage path if it doesn't already exist
		$path = $this->getStoragePath();

		if (!is_dir($path))
		{
			mkdir($path);
		}

		//try to fetch the JSON file
		$file = $path . $this->getOption('name') . '.json';

		if (file_exists($file))
		{
			$json = file_get_contents($file);
			$saveData = json_decode($json);

			//run through the saveData and update the associated fields that we populated from the edit fields
			foreach ($saveData as $field => $value)
			{
				if (array_key_exists($field, $data))
				{
					$data[$field] = $value;
				}
			}
		}

		return $data;
	}

	/**
	 * Attempts to save a settings page
	 *
	 * @param \Illuminate\Http\Request	$input
	 * @param array						$fields
	 *
	 * @return mixed	//string if error, true if success
	 */
	public function save(\Illuminate\Http\Request $input, array $fields)
	{
		$data = array();

		//iterate over the edit fields to only fetch the important items
		foreach ($fields as $name => $field)
		{
			$data[$name] = $input->get($name);

			//make sure the bool field is set correctly
			if ($field->getOption('type') === 'bool')
			{
				$data[$name] = $data[$name] === 'true' || $data[$name] === '1' ? 1 : 0;
			}
		}

		//validate the model
		$validation = $this->validateData($data, $this->getOption('rules'));

		//if a string was kicked back, it's an error, so return it
		if (is_string($validation)) return $validation;

		//run the beforeSave function if provided
		$beforeSave = $this->runBeforeSave($data);

		//if a string was kicked back, it's an error, so return it
		if (is_string($beforeSave)) return $beforeSave;

		//Save the JSON data
		$this->putToJson($data);
		$this->setDataModel($data);

		return true;
	}

	/**
	 * Runs the before save method with the supplied data
	 *
	 * @param array		$data
	 *
	 * @param mixed
	 */
	public function runBeforeSave(array &$data)
	{
		$beforeSave = $this->getOption('before_save');

		if (is_callable($beforeSave))
		{
			$bs = $beforeSave($data);

			//if a string is returned, assume it's an error and kick it back
			if (is_string($bs))
			{
				return $bs;
			}
		}

		return true;
	}

	/**
	 * Puts the data contents into the json file
	 *
	 * @param array		$data
	 */
	public function putToJson($data)
	{
		$path = $this->getStoragePath();

		//check if the storage path is writable
		if (!is_writable($path))
		{
			throw new \InvalidArgumentException("The storage_path option in your " . $this->getOption('name') . " settings config is not writable");
		}

		file_put_contents($path . $this->getOption('name') . '.json', json_encode($data));
	}

}