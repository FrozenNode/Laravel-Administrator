<?php

/**
 * Films model config
 */

return array(

	'title' => 'Films',

	'single' => 'film',

	'model' => 'Film',

	/**
	 * The display columns
	 */
	'columns' => array(
		'id',
		'name',
		'release_date' => array(
			'title' => 'release date'
		),
		'director_name' => array(
			'title' => 'Director Name',
			'relationship' => 'director',
			'select' => "CONCAT((:table).first_name, ' ', (:table).last_name)"
		),
		'num_actors' => array(
			'title' => '# Actors',
			'relationship' => 'actors',
			'select' => "COUNT((:table).id)"
		),
		'box_office' => array(
			'title' => 'Box Office',
			'relationship' => 'boxOffice',
			'select' => "CONCAT('$', FORMAT(SUM((:table).revenue), 2))"
		),
	),

	/**
	 * The filter set
	 */
	'filters' => array(
		'id',
		'name',
		'release_date' => array(
			'title' => 'Release Date',
			'type' => 'date',
			'date_format' => 'yy-mm-dd',
		),
		'director' => array(
			'title' => 'Director',
			'type' => 'relationship',
			'name_field' => 'name',
			'options_sort_field' => "CONCAT(first_name, ' ' , last_name)",
		),
		'actors' => array(
			'title' => 'Actors',
			'type' => 'relationship',
			'name_field' => 'name',
			'options_sort_field' => "CONCAT(first_name, ' ' , last_name)",
		),
	),

	/**
	 * The editable fields
	 */
	'edit_fields' => array(
		'name',
		'release_date' => array(
			'title' => 'Release Date',
			'type' => 'date',
			'date_format' => 'yy-mm-dd',
		),
		'director' => array(
			'title' => 'Director',
			'type' => 'relationship',
			'name_field' => 'name',
			'options_sort_field' => "CONCAT(first_name, ' ' , last_name)",
		),
		'actors' => array(
			'title' => 'Actors',
			'type' => 'relationship',
			'name_field' => 'name',
			'options_sort_field' => "CONCAT(first_name, ' ' , last_name)",
		),
		'theaters' => array(
			'title' => 'Theater',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	),

);