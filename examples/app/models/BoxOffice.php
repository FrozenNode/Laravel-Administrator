<?php

class BoxOffice extends Eloquent {

	protected $table = 'box_office';

	public static $rules = array
	(
		'revenue' => 'required|numeric',
		'film_id' => 'required|integer',
		'theater_id' => 'required|integer',
	);

	public function film()
	{
		return $this->belongsTo('Film');
	}

	public function theater()
	{
		return $this->belongsTo('Theater');
	}

	public function getFormattedRevenueAttribute()
	{
		return '$'.number_format($this->getAttribute('revenue'), 2);
	}
}