<?php

use Illuminate\Database\Migrations\Migration;

class CreateActors extends Migration {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('actors', function($table)
		{
			$table->increments('id');
			$table->text('first_name');
			$table->text('last_name');
			$table->date('birth_date');
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
		Schema::drop('actors');
	}

}