<?php
namespace Frozennode\Administrator\DataTable\Columns;

use Frozennode\Administrator\Config\ConfigInterface;
use Frozennode\Administrator\Traits\OptionableTrait;
use Illuminate\Database\DatabaseManager as DB;

class Column {

	use OptionableTrait;

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
	 * The default configuration options
	 *
	 * @var array
	 */
	protected $defaultOptions = [
		'relationship' => false,
		'sortable' => true,
		'select' => false,
		'output' => '(:value)',
		'sort_field' => null,
		'nested' => [],
		'is_related' => false,
		'is_computed' => false,
		'is_included' => false,
		'external' => false,
		'belongs_to_many' => false,
		'visible' => true,
	];

	/**
	 * The rules that all fields need to pass
	 *
	 * @var array
	 */
	protected $rules = [
		'column_name' => 'required|string',
		'title' => 'string',
		'relationship' => 'string',
		'select' => 'required_with:relationship|string'
	];

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
	 * @param \Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param \Illuminate\Database\DatabaseManager 				$db
	 * @param array												$options
	 */
	public function __construct(ConfigInterface $config, DB $db, array $options)
	{
		$this->config = $config;
		$this->db = $db;
		$this->suppliedOptions = $options;
	}

	/**
	 * Builds the necessary fields on the object
	 *
	 * @param array		$options
	 *
	 * @return array
	 */
	public function buildOptions($options)
	{
		$model = $this->config->getDataModel();
		$this->tablePrefix = $this->db->getTablePrefix();

		//set some options-based defaults
		$options['title'] = array_get($options, 'title', $options['column_name']);
		$options['sort_field'] = array_get($options, 'sort_field', $options['column_name']);

		//if the supplied item is an accessor, make this unsortable for the moment
		if (method_exists($model, camel_case('get_'.$options['column_name'].'_attribute')) && $options['column_name'] === $options['sort_field'])
		{
			$options['sortable'] = false;
		}

		//however, if this is not a relation and the select option was supplied, str_replace the select option and make it sortable again
		if ($select = array_get($options, 'select'))
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
		$visible = array_get($options, 'visible');

		if (is_callable($visible))
		{
			$options['visible'] = $visible($this->config->getDataModel()) ? true : false;
		}

		return $options;
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
}