<?php namespace Frozennode\Administrator\Field\Types;

class Date extends Time {

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'date';
	}
}