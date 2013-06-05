<?php

class Theater extends Model {

	public static $rules = array
	(
		'name' => 'required',
	);

	public function films()
	{
		return $this->hasManyAndBelongsTo('Film', 'films_theaters');
	}

	public function box_office()
	{
		return $this->hasMany('BoxOffice');
	}
}