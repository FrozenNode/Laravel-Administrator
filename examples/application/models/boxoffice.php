<?php

class BoxOffice extends Aware {

	public static $table = 'box_office';

	public static $rules = array
	(
		'revenue' => 'required|numeric',
		'film_id' => 'required|integer',
		'theater_id' => 'required|integer',
	);

	public function film()
	{
		return $this->belongs_to('Film');
	}

	public function theater()
	{
		return $this->belongs_to('Theater');
	}

	public function get_formatted_revenue()
	{
		return '$'.number_format($this->get_attribute('revenue'), 2);
	}
}