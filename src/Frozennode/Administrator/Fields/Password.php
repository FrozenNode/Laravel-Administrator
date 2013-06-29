<?php
namespace Frozennode\Administrator\Fields;

class Password extends Text {

	/**
	 * When a field is a setter, no value will be returned from the database and the value will be unset before saving
	 *
	 * @var bool
	 */
	public $setter = true;

	/**
	 * Constructor function
	 *
	 * @param string|int	$field
	 * @param array|string	$info
	 * @param ModelConfig 	$config
	 */
	public function __construct($field, $info, $config)
	{
		parent::__construct($field, $info, $config);

		$this->setter = array_get($info, 'setter', $this->setter);
	}
}