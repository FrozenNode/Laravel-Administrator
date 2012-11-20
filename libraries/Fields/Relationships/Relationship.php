<?php
namespace Admin\Libraries\Fields\Relationships;

use Admin\Libraries\Fields\Field;

abstract class Relationship extends Field {


	/**
	 * This is used in setting up filters
	 *
	 * @var bool
	 */
	public $relationship = true;

	/**
	 * If this is true, the field is an external field (i.e. it's a relationship but not a belongs_to)
	 *
	 * @var bool
	 */
	public $external = true;

	/**
	 * The string to use to name the items on the other table
	 *
	 * @var string
	 */
	public $nameField = 'name';

	/**
	 * The symbol to use in front of the number
	 *
	 * @var string
	 */
	public $table = '';

	/**
	 * The number of decimal places after the number
	 *
	 * @var string
	 */
	public $column = '';

	/**
	 * Foreign key value that is used on the local table
	 *
	 * @var bool|string
	 */
	public $foreignKey = false;

	/**
	 * This determines if there are potentially multiple related values (i.e. whether to use an array of items or just a single value)
	 *
	 * @var bool
	 */
	public $multipleValues = false;

	/**
	 * The array of items from which the user will be able to choose
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param Eloquent 		$model
	 */
	public function __construct($field, $info, $model)
	{
		parent::__construct($field, $info, $model);

		//get an instance of the relationship object
		$relationship = $model->{$field}();

		//get the name field option
		$this->nameField = $info['name_field'] = array_get($info, 'name_field', $this->nameField);

		$this->options = array_map(function($m) use ($info, $model)
		{
			return array(
				$model::$key => $m->{$model::$key},
				$info['name_field'] => $m->{$info['name_field']},
			);
		}, $relationship->model->all());
	}

	/**
	 * Turn this item into an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$arr = parent::toArray();

		$arr['table'] = $this->table;
		$arr['column'] = $this->column;
		$arr['name_field'] = $this->nameField;
		$arr['options'] = $this->options;

		return $arr;
	}
}