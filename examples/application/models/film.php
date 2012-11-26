<?php

class Film extends Aware {

	public static $rules = array
	(
		'name' => 'required',
		'release_date' => 'required',
		'director_id' => 'required',
	);

	public function director()
	{
		return $this->belongs_to('Director');
	}

	public function actors()
	{
		return $this->has_many_and_belongs_to('Actor', 'actors_films');
	}

	public function theaters()
	{
		return $this->has_many_and_belongs_to('Theater', 'films_theaters');
	}

	public function box_office()
	{
		return $this->has_many('BoxOffice');
	}

	public function get_director_name()
	{
		return isset($this->director->name) ? $this->director->name : '';
	}
}