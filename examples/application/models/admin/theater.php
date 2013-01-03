<?php
namespace AdminModels;

class Theater extends \Theater {

	public $columns = array
	(
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
	);

	public $filters = array
	(
		'id',
		'name',
		'films' => array(
			'title' => 'Films',
			'type' => 'relationship',
			'name_field' => 'name',
		),
	);

	public $edit = array
	(
		'name' => array(
			'title' => 'Name',
			'type' => 'text',
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