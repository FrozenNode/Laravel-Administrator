<?php

use Illuminate\Database\Migrations\Migration;

class CreateDirectorsTable extends Migration {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('directors', function($table)
		{
			$table->increments('id');
			$table->string('first_name');
			$table->string('last_name');
			$table->decimal('salary', 10, 2);
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
		Schema::drop('directors');
	}

}