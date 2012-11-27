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
	 * If this is true, the field will start with no options and be an autocomplete
	 *
	 * @var bool
	 */
	public $autocomplete = false;

	/**
	 * The number of options to display to a user when the autocomplete is turned on
	 *
	 * @var int
	 */
	public $numOptions = 10;

	/**
	 * The search fields on the other table to look for when autocomplete is on. If left empty, default is the name_field
	 *
	 * @var array
	 */
	public $searchFields = array();

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
		$this->nameField = array_get($info, 'name_field', $this->nameField);
		$this->autocomplete = array_get($info, 'autocomplete', $this->autocomplete);
		$this->numOptions = array_get($info, 'num_options', $this->numOptions);
		$this->searchFields = array_get($info, 'search_fields', array($this->nameField));

		//if we want all of the options, load them up, otherwise leave it an empty array
		if (array_get($info, 'load_options', false))
		{
			$this->options = array_map(function($m) use ($info, $model)
			{
				return array(
					$model::$key => $m->{$model::$key},
					$info['name_field'] => $m->{$info['name_field']},
				);
			}, $relationship->model->all());
		}
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
		$arr['autocomplete'] = $this->autocomplete;
		$arr['num_options'] = $this->numOptions;
		$arr['search_fields'] = $this->searchFields;

		return $arr;
	}
}