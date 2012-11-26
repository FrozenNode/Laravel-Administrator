<?php

class Actor extends Aware {

	public static $rules = array
	(
		'first_name' => 'required',
		'last_name' => 'required',
		'birth_date' => 'required',
	);

	public function films()
	{
		return $this->has_many_and_belongs_to('Film', 'actors_films');
	}

	public function get_formatted_birth_date()
	{
		return date('Y-m-d', strtotime($this->get_attribute('birth_date')));
	}

	public function get_name()
	{
		return $this->get_attribute('first_name').' '.$this->get_attribute('last_name');
	}
}