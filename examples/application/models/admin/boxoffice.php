<?php
namespace AdminModels;

class BoxOffice extends \BoxOffice {

	public $columns = array
	(
		'id',
		'formatted_revenue' => array(
			'title' => 'Revenue',
			'sort_field' => 'revenue',
		),
		'film' => array(
			'title' => 'Film',
			'relationship' => 'film',
			'select' => '(:table).name',
		),
		'theater' => array(
			'title' => 'Theater',
			'relationship' => 'theater',
			'select' => '(:table).name',
		),
	);

	public $filters = array
	(
		'id',
		'revenue' => array(
			'title' => 'Revenue',
			'type' => 'number',
			'symbol' => '$',
			'decimals' => 2,
		),
		'film' => array(
			'title' => 'Film',
			'type' => 'relationship',
			'name_field' => 'name',
		),
		'theater' => array(
			'title' => 'Theater',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	);

	public $edit = array
	(
		'revenue' => array(
			'title' => 'Revenue',
			'type' => 'number',
			'symbol' => '$',
			'decimals' => 2,
		),
		'film' => array(
			'title' => 'Film',
			'type' => 'relationship',
			'name_field' => 'name',
		),
		'theater' => array(
			'title' => 'Theater',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	);
}