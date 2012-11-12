<?php
namespace Admin\Libraries;

/**
 * The Column class helps us construct columns from models. It can be used to derive column information from a model, or it can be instantiated to hold information about
 * any given column.  
 */
class Column {
	
	public $key;
	
	public $title;
	
	public $description;
	
	
	/**
	 * The constructor takes a key, title, and description for a column. Only the key is necessary
	 * 
	 * @param string	$key
	 * @param string	$title
	 * @param string	$description
	 */
	public function __construct($key, $title = null, $description = "")
	{
		$this->description = $description;
		$this->title = $title;
		$this->key = $key;

		if (!$title)
		{
			$this->title = $key;
		}
	}
}
