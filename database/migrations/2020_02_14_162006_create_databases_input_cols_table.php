<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDatabasesInputColsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('databases_input_cols', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('databases_inputs_id');
			$table->integer('databases_columns_id');
			$table->text('value', 65535)->nullable();
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
		Schema::drop('databases_input_cols');
	}

}
