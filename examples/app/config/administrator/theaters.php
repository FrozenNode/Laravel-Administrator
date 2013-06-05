<?php

/**
 * Directors model config
 */

return array(

	'title' => 'Directors',

	'single' => 'director',

	'model' => 'Director',

	/**
	 * The display columns
	 */
	'columns' => array(
		'id',
		'name' => array(
			'title' => 'Name',
		),
		'num_films' => array(
			'title' => '# films',
			'relationship' => 'films',
			'select' => 'COUNT((:table).id)',
		),
		'box_office' => array(
			'title' => 'Box Office',
			'relationship' => 'box_office',
			'select' => "CONCAT('$', FORMAT(SUM((:table).revenue), 2))"
		),
	),

	/**
	 * The filter set
	 */
	'filters' => array(
		'id',
		'name',
		'films' => array(
			'title' => 'Films',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	),

	/**
	 * The editable fields
	 */
	'edit_fields' => array(
		'name' => array(
			'title' => 'Name',
			'type' => 'text',
		),
		'films' => array(
			'title' => 'Films',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	),

);