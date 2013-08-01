<?php

class CreateFilmsTable {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('films', function($table)
		{
			$table->increments('id');
			$table->string('name');
			$table->date('release_date');
			$table->integer('director_id');
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
		Schema::drop('films');
	}

}