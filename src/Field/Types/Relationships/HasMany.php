<?php namespace Frozennode\Administrator\Field\Types\Relationships;

class HasMany extends HasOneOrMany {

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = [
		'multiple_values' => true,
		'editable' => false,
	];

	/**
	 * Abstract method that should return a field's string representation in the config files
	 *
	 * @return string
	 */
	public function getConfigName()
	{
		return 'has_many';
	}
}