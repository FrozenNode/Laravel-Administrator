<?php
namespace AdminModels;

class Actor extends \Actor {

	public $columns = array
	(
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
	);

	public $filters = array
	(
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
		'birth_date' => array(
			'title' => 'Birth Date',
			'type' => 'date',
		),
		'films' => array(
			'title' => 'Films',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	);

	public function on_delete()
	{
		$this->films()->delete();
	}
}