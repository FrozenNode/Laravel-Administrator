<?php
namespace Frozennode\Administrator\Config;

interface ConfigInterface {

	/**
	 * Fetches the data model for a config
	 *
	 * @return  mixed
	 */
	public function getDataModel();

	/**
	 * Sets the data model for a config
	 *
	 * @param  $model
	 *
	 * @return  void
	 */
	public function setDataModel($model);

	/**
	 * Gets a config option from the supplied array
	 *
	 * @param string	$key
	 *
	 * @return mixed
	 */
	public function getOption($key);

	/**
	 * Saves the data
	 *
	 * @param \Illuminate\Http\Request	$input
	 * @param array						$fields
	 */
	public function save(\Illuminate\Http\Request $input, array $fields);

	/**
	 * Gets the config type
	 */
	public function getType();

}