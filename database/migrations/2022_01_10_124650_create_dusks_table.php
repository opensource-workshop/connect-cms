<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDusksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dusks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('category', 255)->comment('カテゴリ');
            $table->string('sort', 255)->comment('ソート順');
            $table->string('plugin', 255)->comment('プラグイン名');
            $table->string('method', 255)->comment('メソッド名');
            $table->text('test_result')->nullable()->comment('テスト結果');
            $table->string('html_path', 255)->comment('HTMLパス');
            $table->text('function_title')->nullable()->comment('機能名');
            $table->text('method_desc')->nullable()->comment('機能概要');
            $table->text('function_desc')->nullable()->comment('機能詳細');
            $table->integer('created_id')->nullable();
            $table->string('created_name', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('updated_id')->nullable();
            $table->string('updated_name', 255)->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('deleted_id')->nullable();
            $table->string('deleted_name', 255)->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dusks');
    }
}
