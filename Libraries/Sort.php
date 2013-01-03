<?php
namespace Admin\Libraries;

class Sort {

	/**
	 * The name of the field
	 *
	 * @var string
	 */
	public $field;

	/**
	 * The field type which matches a $fieldTypes key
	 *
	 * @var string
	 */
	public $direction = 'desc';

	/**
	 * Constructor function
	 *
	 * @param string	$field
	 * @param string	$direction
	 */
	public function __construct($field, $direction)
	{
		$this->field = $field;
		$this->direction = $direction;
	}


	/**
	 * Takes a the key/value of the options array and the associated model and returns an instance of the field
	 *
	 * @param Eloquent 		$model 		//an instance of the Eloquent model
	 * @param string		$field 		//the field to sort
	 * @param string		$direction	//either 'asc' or 'desc'
	 *
	 * @return false|Field object
	 */
	public static function get($model, $field = false, $direction = false)
	{
		//if the model sort options aren't set up, make it the default fields
		$modelSort = isset($model->sortOptions) ? $model->sortOptions : array('field' => $model::$key, 'direction' => 'desc');

		if (!$field || !is_string($field))
		{
			$field = array_get($modelSort, 'field', $model::$key);
		}

		if (!$direction || !in_array($direction, array('asc', 'desc')))
		{
			$direction = array_get($modelSort, 'direction', 'desc');
		}

		//now we can instantiate the object
		return new static($field, $direction, $model);
	}

	/**
	 * Turn sort options into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'field' => $this->field,
			'direction' => $this->direction,
		);
	}
}