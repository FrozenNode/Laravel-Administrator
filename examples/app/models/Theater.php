<?php

class Theater extends Eloquent {

	public static $rules = array
	(
		'name' => 'required',
	);

	public function films()
	{
		return $this->belongsToMany('Film', 'films_theaters');
	}

	public function boxOffice()
	{
		return $this->hasMany('BoxOffice');
	}
}