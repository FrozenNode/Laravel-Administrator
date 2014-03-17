<?php namespace Frozennode\Administrator;

use Frozennode\Administrator\Field\Factory as FieldFactory;
use Frozennode\Administrator\Field\Field;

class Manager {

	/**
	 * The field factory instance
	 *
	 * @var \Frozennode\Administrator\Field\Factory
	 */
	protected $fieldFactory;

	/**
	 * Creates a new Manager instance
	 *
	 * @param \Frozennode\Administrator\Field\Factory	$fieldFactory
	 */

	/**
	 * Accepts a field and registers it with
	 *
	 * @param string	$abstract
	 */
	public function registerField($abstract)
	{
		//register the abstract with the field factory
		$this->fieldFactory->registerField($abstract);
	}

}