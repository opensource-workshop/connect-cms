<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDatabasesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('databases', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('bucket_id');
			$table->string('databases_name');
			$table->integer('mail_send_flag')->default(0);
			$table->text('mail_send_address', 65535)->nullable();
			$table->integer('user_mail_send_flag')->default(0);
			$table->string('from_mail_name')->nullable();
			$table->string('mail_subject')->nullable();
			$table->text('mail_databaseat', 65535)->nullable();
			$table->integer('data_save_flag')->default(0);
			$table->text('after_message', 65535)->nullable();
			$table->integer('numbering_use_flag')->default(0)->comment('採番使用フラグ');
			$table->string('numbering_prefix')->nullable()->comment('採番プレフィックス');
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
		Schema::drop('databases');
	}

}
