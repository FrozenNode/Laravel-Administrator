<?php

class Theater extends Aware {

	public static $rules = array
	(
		'name' => 'required',
	);

	public function films()
	{
		return $this->has_many_and_belongs_to('Film', 'films_theaters');
	}

	public function box_office()
	{
		return $this->has_many('BoxOffice');
	}
}