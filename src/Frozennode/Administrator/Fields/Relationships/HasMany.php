<?php
namespace Frozennode\Administrator\Fields\Relationships;

class HasMany extends HasOneOrMany {

	/**
	 * The relationship-type-specific defaults for the relationship subclasses to override
	 *
	 * @var array
	 */
	protected $relationshipDefaults = array(
		'multiple_values' => true,
		'editable' => false,
	);
}