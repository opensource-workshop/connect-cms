<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDatabasesColumnsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('databases_columns', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('databases_id');
			$table->string('column_type');
			$table->string('column_name');
			$table->integer('required');
			$table->integer('frame_col')->nullable();
			$table->string('caption')->nullable()->comment('キャプション');
			$table->string('caption_color')->default('text-dark')->comment('キャプション文字色');
			$table->integer('minutes_increments')->default(10)->comment('分刻み指定');
			$table->string('rule_allowed_numeric')->nullable()->comment('数値のみ許容');
			$table->string('rule_allowed_alpha_numeric')->nullable()->comment('英数値のみ許容');
			$table->string('rule_digits_or_less')->nullable()->comment('指定桁数以下を許容');
			$table->string('rule_max')->nullable()->comment('最大値設定');
			$table->string('rule_min')->nullable()->comment('最小値設定');
			$table->string('rule_word_count')->nullable()->comment('最大文字数（半角換算）');
			$table->string('rule_date_after_equal')->nullable()->comment('～日以降を許容');
			$table->integer('display_sequence')->unsigned()->nullable();
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
		Schema::drop('databases_columns');
	}
}
