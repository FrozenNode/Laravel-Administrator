<?php
namespace Frozennode\Administrator\DataTable\Columns;

use Frozennode\Administrator\Validator;
use Frozennode\Administrator\Config\ConfigInterface;
use Illuminate\Database\DatabaseManager as DB;

class Factory {

	/**
	 * The validator instance
	 *
	 * @var Frozennode\Administrator\Validator
	 */
	protected $validator;

	/**
	 * The config instance
	 *
	 * @var Frozennode\Administrator\Config\ConfigInterface
	 */
	protected $config;

	/**
	 * The config instance
	 *
	 * @var Illuminate\Database\DatabaseManager
	 */
	protected $db;

	/**
	 * The column objects
	 *
	 * @var array
	 */
	protected $columns = array();

	/**
	 * The column arrays
	 *
	 * @var array
	 */
	protected $columnArrays = array();

	/**
	 * The included column (used for pulling a certain range of selects from the DB)
	 *
	 * @var array
	 */
	protected $includedColumns = array();

	/**
	 * The relationship columns
	 *
	 * @var array
	 */
	protected $relatedColumns = array();

	/**
	 * The computed columns (either an accessor or a select was supplied)
	 *
	 * @var array
	 */
	protected $computedColumns = array();

	/**
	 * Create a new action Factory instance
	 *
	 * @param Frozennode\Administrator\Validator 				$validator
	 * @param Frozennode\Administrator\Config\ConfigInterface	$config
	 * @param Illuminate\Database\DatabaseManager 				$db
	 */
	public function __construct(Validator $validator, ConfigInterface $config, DB $db)
	{
		//set the config, and then validate it
		$this->config = $config;
		$this->validator = $validator;
		$this->db = $db;
	}

	/**
	 * Fetches a Column instance from the supplied options
	 *
	 * @param array		$options
	 *
	 * @return Administrator\Frozennode\DataTable\Columns\Column
	 */
	public function make($options)
	{
		return $this->getColumnObject($options);
	}

	/**
	 * Creates the Column instance
	 *
	 * @param array		$options
	 *
	 * @return Administrator\Frozennode\DataTable\Columns\Column
	 */
	public function getColumnObject($options)
	{
		$column = new Column($this->validator, $this->config, $this->db, $options);
		$column->build();

		return $column;
	}

	/**
	 * Parses an options array and a string name and returns an options array with the column_name option set
	 *
	 * @param mixed		$name
	 * @param mixed		$options
	 *
	 * @return array
	 */
	public function parseOptions($name, $options)
	{
		if (is_string($options))
		{
			$name = $options;
			$options = array();
		}

		//if the name is not a string or the options is not an array at this point, throw an error because we can't do anything with it
		if (!is_string($name) || !is_array($options))
		{
			throw new \InvalidArgumentException("One of the columns in your " . $this->config->getOption('name') . " model configuration file is invalid");
		}

		//in any case, make sure the 'column_name' option is set
		$options['column_name'] = $name;

		return $options;
	}

	/**
	 * Gets the column objects
	 *
	 * @return array
	 */
	public function getColumns()
	{
		//make sure we only run this once and then return the cached version
		if (!sizeof($this->columns))
		{
			foreach ($this->config->getOption('columns') as $name => $options)
			{
				//if only a string value was supplied, may sure to turn it into an array
				$object = $this->make($this->parseOptions($name, $options));
				$this->columns[$name] = $object;
			}
		}

		return $this->columns;
	}

	/**
	 * Gets the column objects as an integer-indexed array
	 *
	 * @return array
	 */
	public function getColumnValues()
	{
		return array_values($this->getColumns());
	}

	/**
	 * Gets the column objects as an integer-indexed array
	 *
	 * @return array
	 */
	public function getColumnArrays()
	{
		//make sure we only run this once and then return the cached version
		if (!sizeof($this->columnArrays))
		{
			foreach ($this->getColumns() as $column)
			{
				$this->columnArrays[] = $columnObject->toArray();
			}
		}

		return $this->columnArrays;
	}

	/**
	 * Gets the columns that are on the model's table (i.e. not related or computed)
	 *
	 * @param array		$fields
	 *
	 * @return array
	 */
	public function getIncludedColumns(array $fields)
	{
		//make sure we only run this once and then return the cached version
		if (!sizeof($this->includedColumns))
		{
			$model = $this->config->getDataModel();

			foreach ($this->getColumns() as $column)
			{
				if ($column->isRelated)
				{
					//if there are nested values, we'll want to grab the values slightly differently
					if (sizeof($column->nested))
					{
						$fk = $column->nested['models'][0]->{$column->nested['pieces'][0]}()->getForeignKey();
						$this->includedColumns[$fk] = $model->getTable().'.'.$fk;
					}
					else if ($column->belongsToMany)
					{
						$fk = $model->{$column->relationship}()->getRelated()->getKeyName();
						$this->includedColumns[$fk] = $model->getTable().'.'.$fk;
					}
				}
				else if (!$column->isComputed)
				{
					$this->includedColumns[$column->field] = $model->getTable().'.'.$column->field;
				}
			}

			//make sure the table key is included
			if (!array_get($this->includedColumns, $model->getKeyName()))
			{
				$this->includedColumns[$model->getKeyName()] = $model->getTable().'.'.$model->getKeyName();
			}

			//make sure any belongs_to fields that aren't on the columns list are included
			foreach ($fields as $field)
			{
				if (is_a($field, 'Frozennode\\Administrator\\Fields\\Relationships\\BelongsTo'))
				{
					$this->includedColumns[$field->foreignKey] = $model->getTable().'.'.$field->foreignKey;
				}
			}
		}

		return $this->includedColumns;
	}

	/**
	 * Gets the columns that are relationship columns
	 *
	 * @return array
	 */
	public function getRelatedColumns()
	{
		//make sure we only run this once and then return the cached version
		if (!sizeof($this->relatedColumns))
		{
			foreach ($this->getColumns() as $column)
			{
				if ($column->isRelated)
				{
					$this->relatedColumns[$column->field] = $column->field;
				}
			}
		}

		return $this->relatedColumns;
	}

	/**
	 * Gets the columns that are computed
	 *
	 * @return array
	 */
	public function getComputedColumns()
	{
		//make sure we only run this once and then return the cached version
		if (!sizeof($this->computedColumns))
		{
			foreach ($this->getColumns() as $column)
			{
				if (!$column->isRelated && $column->isComputed)
				{
					$this->computedColumns[$column->field] = $column->field;
				}
			}
		}

		return $this->computedColumns;
	}

}