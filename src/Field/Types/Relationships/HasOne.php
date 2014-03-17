<?php namespace Frozennode\Administrator\Field\Types\Relationships;

class HasOne extends HasOneOrMany {

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'has_one';
	}

}
