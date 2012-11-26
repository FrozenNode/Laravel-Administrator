<?php

class Create_Theaters {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('theaters', function($table)
		{
			$table->increments('id');
			$table->text('name');
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
		Schema::drop('theaters');
	}

}