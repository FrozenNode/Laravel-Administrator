<?php

/**
 * Actors model config
 */

return array(

	'title' => 'Actors',

	'single' => 'actor',

	'model' => 'Actor',

	/**
	 * The display columns
	 */
	'columns' => array(
		'id',
		'full_name' => array(
			'title' => 'Name',
			'select' => "CONCAT((:table).first_name, ' ', (:table).last_name)",
		),
		'num_films' => array(
			'title' => '# films',
			'relationship' => 'films',
			'select' => 'COUNT((:table).id)',
		),
		'formatted_birth_date' => array(
			'title' => 'Birth Date',
			'sort_field' => 'birth_date',
		),
	),

	/**
	 * The filter set
	 */
	'filters' => array(
		'id',
		'first_name' => array(
			'title' => 'First Name',
		),
		'last_name' => array(
			'title' => 'Last Name',
		),
		'films' => array(
			'title' => 'Films',
			'type' => 'relationship',
			'name_field' => 'name',
		),
		'birth_date' => array(
			'title' => 'Birth Date',
			'type' => 'date'
		),
	),

	/**
	 * The editable fields
	 */
	'edit_fields' => array(
		'first_name' => array(
			'title' => 'First Name',
			'type' => 'text',
		),
		'last_name' => array(
			'title' => 'Last Name',
			'type' => 'text',
		),
		'birth_date' => array(
			'title' => 'Birth Date',
			'type' => 'date',
		),
		'films' => array(
			'title' => 'Films',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	),

);