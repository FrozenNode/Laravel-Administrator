<?php
namespace Frozennode\Administrator\DataTable\Columns;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class Column {

	/**
	 * The validator instance
	 *
	 * @var \Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The config instance
	 *
	 * @var \Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The config instance
	 *
	 * @var \Illuminate\Database\DatabaseManager
	 */
	protected $db;

	/**
	 * The options array
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * The originally-supplied options array
	 *
	 * @var array
	 */
	protected $suppliedOptions;

	/**
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $baseDefaults = array(
		'relationship' => false,
		'sortable' => true,
		'select' => false,
		'output' => '(:value)',
		'sort_field' => null,
		'nested' => array(),
		'is_related' => false,
		'is_computed' => false,
		'is_included' => false,
		'external' => false,
		'belongs_to_many' => false,
		'visible' => true,
	);

	/**
	 * The specific defaults for subclasses to override
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * The base rules that all fields need to pass
	 *
	 * @var array
	 */
	protected $baseRules = array(
		'column_name' => 'required|string',
		'title' => 'string',
		'relationship' => 'string',
		'select' => 'required_with:relationship|string'
	);

	/**
	 * The specific rules for subclasses to override
	 *
	 * @var array
	 */
	protected $rules = array();

	/**
	 * The immediate relationship object for this column
	 *
	 * @var Relationship
	 */
	protected $relationshipObject = NULL;

	/**
	 * The table prefix
	 *
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * Create a new action Factory instance
	 *
	 * @param \Frozennode\Administrator\Validator 				$validator
	 * @param \Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param \Illuminate\Database\DatabaseManager 				$db
	 * @param array												$options
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db, array $options)
	{
		$this->config = $config;
		$this->validator = $validator;
		$this->db = $db;
		$this->suppliedOptions = $options;
	}

	/**
	 * Validates the supplied options
	 *
	 * @return void
	 */
	public function validateOptions()
	{
		//override the config
		$this->validator->override($this->suppliedOptions, $this->getRules());

		//if the validator failed, throw an exception
		if ($this->validator->fails())
		{
			throw new \InvalidArgumentException("There are problems with your '" . $this->suppliedOptions['column_name'] . "' column in the " .
									$this->config->getOption('name') . " model: " .	implode('. ', $this->validator->messages()->all()));
		}
	}

	/**
	 * Builds the necessary fields on the object
	 *
	 * @return void
	 */
	public function build()
	{
		$model = $this->config->getDataModel();
		$options = $this->suppliedOptions;
		$this->tablePrefix = $this->db->getTablePrefix();

		//set some options-based defaults
		$options['title'] = $this->validator->arrayGet($options, 'title', $options['column_name']);
		$options['sort_field'] = $this->validator->arrayGet($options, 'sort_field', $options['column_name']);

		//if the supplied item is an accessor, make this unsortable for the moment
		if (method_exists($model, camel_case('get_'.$options['column_name'].'_attribute')) && $options['column_name'] === $options['sort_field'])
		{
			$options['sortable'] = false;
		}

		//however, if this is not a relation and the select option was supplied, str_replace the select option and make it sortable again
		if ($select = $this->validator->arrayGet($options, 'select'))
		{
			$options['select'] = str_replace('(:table)', $this->tablePrefix . $model->getTable(), $select);
		}

		//now we do some final organization to categorize these columns (useful later in the sorting)
		if (method_exists($model, camel_case('get_'.$options['column_name'].'_attribute')) || $select)
		{
			$options['is_computed'] = true;
		}
		else
		{
			$options['is_included'] = true;
		}

		//run the visible property closure if supplied
		$visible = $this->validator->arrayGet($options, 'visible');

		if (is_callable($visible))
		{
			$options['visible'] = $visible($this->config->getDataModel()) ? true : false;
		}

		$this->suppliedOptions = $options;
	}

	/**
	 * Adds selects to a query
	 *
	 * @param array 	$selects
	 *
	 * @return void
	 */
	public function filterQuery(&$selects)
	{
		if ($select = $this->getOption('select'))
		{
			$selects[] = $this->db->raw($select . ' AS ' . $this->db->getQueryGrammar()->wrap($this->getOption('column_name')));
		}
	}

	/**
	 * Gets all user options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		//make sure the supplied options have been merged with the defaults
		if (empty($this->options))
		{
			//validate the options and build them
			$this->validateOptions();
			$this->build();
			$this->options = array_merge($this->getDefaults(), $this->suppliedOptions);
		}

		return $this->options;
	}

	/**
	 * Gets a field's option
	 *
	 * @param string 	$key
	 *
	 * @return mixed
	 */
	public function getOption($key)
	{
		$options = $this->getOptions();

		if (!array_key_exists($key, $options))
		{
			throw new \InvalidArgumentException("An invalid option was searched for in the '" . $options['column_name'] . "' column");
		}

		return $options[$key];
	}

	/**
	 * Takes a column output string and renders the column with it (replacing '(:value)' with the column's field value)
	 *
	 * @param string	$value
	 *
	 * @return string
	 */
	public function renderOutput($value)
	{
		$output = $this->getOption('output');
		
		if (is_callable($output)) {
			return $output($value);
		}
		
		return str_replace('(:value)', $value, $output);
	}

	/**
	 * Gets all rules
	 *
	 * @return array
	 */
	public function getRules()
	{
		return array_merge($this->baseRules, $this->rules);
	}

	/**
	 * Gets all default values
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		return array_merge($this->baseDefaults, $this->defaults);
	}
}
