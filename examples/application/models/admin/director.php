<?php
namespace AdminModels;

class Director extends \Director {

	public $columns = array
	(
		'name' => array(
			'title' => 'Name',
		),
		'formatted_salary' => array(
			'title' => 'Salary',
			'sort_field' => 'salary'
		),
		'num_films' => array(
			'title' => '# films',
			'relation' => 'films',
			'select' => 'COUNT((:table).id)',
		),
		'created_at',
	);

	public $filters = array
	(
		'id',
		'first_name',
		'last_name',
		'salary' => array(
			'type' => 'number',
			'symbol' => '$',
			'decimals' => 2,
		),
		'created_at' => array(
			'type' => 'datetime'
		),
	);

	public $edit = array
	(
		'first_name' => array(
			'title' => 'First Name',
			'type' => 'text',
		),
		'last_name' => array(
			'title' => 'Last Name',
			'type' => 'text',
		),
		'salary' => array(
			'title' => 'Salary',
			'type' => 'number',
			'symbol' => '$',
			'decimals' => 2
		),
	);

	public function on_delete()
	{
		$this->films()->delete();
	}
}