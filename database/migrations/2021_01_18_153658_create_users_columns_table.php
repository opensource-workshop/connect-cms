<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_columns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_id')->unsigned()->comment('users.id');
            $table->string('column_type', 255);
            $table->string('column_name', 255);
            $table->integer('required');
            $table->string('caption', 255)->nullable()->comment('キャプション');
            $table->string('caption_color', 255)->default('text-dark')->comment('キャプション文字色');
            $table->string('place_holder', 255)->nullable()->comment('プレースホルダ―');

            // $table->integer('minutes_increments')->default(10)->comment('分刻み指定');           // 時間型の追加ルール, 一旦実装しない
            $table->string('rule_allowed_numeric', 255)->nullable()->comment('数値のみ許容');
            $table->string('rule_allowed_alpha_numeric', 255)->nullable()->comment('英数値のみ許容');
            $table->string('rule_digits_or_less', 255)->nullable()->comment('指定桁数以下を許容');
            $table->string('rule_max', 255)->nullable()->comment('最大値設定');
            $table->string('rule_min', 255)->nullable()->comment('最小値設定');
            $table->string('rule_regex', 255)->nullable()->comment('正規表現設定');
            $table->string('rule_word_count', 255)->nullable()->comment('最大文字数（半角換算）');
            // $table->string('rule_date_after_equal', 255)->nullable()->comment('～日以降を許容');   // 日付型の追加ルール, 一旦実装しない

            // 一旦実装しない
            // $table->integer('role_display_control_flag')->default(0)->comment('権限で表示カラムを制御');
            // $table->integer('row_group')->comment('行グループ')->nullable();
            // $table->integer('column_group')->comment('列グループ')->nullable();

            $table->integer('display_sequence')->default('0')->comment('表示順');

            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_columns');
    }
}
