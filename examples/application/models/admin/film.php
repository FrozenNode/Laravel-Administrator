<?php
namespace AdminModels;

class Film extends \Film {

	public $columns = array
	(
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
			'relationship' => 'box_office',
			'select' => "CONCAT('$', FORMAT(SUM((:table).revenue), 2))"
		),
	);

	public $filters = array(
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
		),
		'actors' => array(
			'title' => 'Actors',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	);

	public $edit = array
	(
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
		),
		'actors' => array(
			'title' => 'Actors',
			'type' => 'relationship',
			'name_field' => 'name',
		),
		'theaters' => array(
			'title' => 'Theater',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	);

}