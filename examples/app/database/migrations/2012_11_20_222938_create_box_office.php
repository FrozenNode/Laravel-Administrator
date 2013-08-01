<?php

use Illuminate\Database\Migrations\Migration;

class CreateBoxOffice extends Migration {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('box_office', function($table)
		{
			$table->increments('id');
			$table->decimal('revenue', 10, 2);
			$table->integer('film_id')->unsigned();
			$table->integer('theater_id')->unsigned();
			$table->timestamps();
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('box_office');
	}

}