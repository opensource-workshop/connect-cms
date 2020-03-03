<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDatabasesColumnsSelectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('databases_columns_selects', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('databases_columns_id');
			$table->string('value');
			$table->string('caption')->nullable();
			$table->string('default')->nullable();
			$table->integer('display_sequence')->comment('表示順');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('databases_columns_selects');
	}

}
