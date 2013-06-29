<?php
namespace Frozennode\Administrator\Fields;

class Password extends Text {

	/**
	 * When a field is a setter, no value will be returned from the database and the value will be unset before saving
	 *
	 * @var bool
	 */
	public $setter = true;
}