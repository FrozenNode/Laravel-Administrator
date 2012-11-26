<?php

class Director extends Aware {
	
	public $includes = array('films');

	public static $rules = array
	(
		'first_name' => 'required',
		'last_name' => 'required',
		'salary' => 'required',
	);

	public function films()
	{
		return $this->has_many('Film');
	}

	public function get_name()
	{
		return $this->get_attribute('first_name') . ' ' . $this->get_attribute('last_name');
	}

	public function get_formatted_salary()
	{
		return '$'.number_format($this->get_attribute('salary'), 2);
	}

	public function get_num_films()
	{
		return count($this->films);
	}
}