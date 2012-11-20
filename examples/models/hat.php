<?php
namespace AdminModels

class Hat extends \Hat
{
	public static $per_page = 10;

	public $columns = array(
		'id',
		'name',
		'price',
		'start_date',
		'start_time',
		'inception',
		'created_at' => array(
			'title' => 'created'
		),
		'updated_at' => array(
			'title' => 'updated'
		),
	);

	public $sortOptions = array(
		'field' => 'id',
		'direction' => 'desc',
	);

	public $edit = array(
		'name',
		'price' => array(
			'title' => 'Price',
			'type' => 'currency',
			'symbol' => '$',
			'decimals' => 2,
		),
		'start_date' => array(
			'title' => 'Start Date',
			'type' => 'date',
			'date_format' => 'yy-mm-dd',
		),
		'start_time' => array(
			'title' => 'Start Time',
			'type' => 'time',
			'time_format' => 'HH:mm',
		),
		'inception' => array(
			'title' => 'Inception',
			'type' => 'datetime',
			'time_format' => 'HH:mm',
			'date_format' => 'yy-mm-dd',
		),
		'roles' => array(
			'type' => 'relation',
			'title_field' => 'name',
		),
	);

	public $filters = array(
		'name',
		'users' => array(
			'type' => 'relation'
		),
	);

}