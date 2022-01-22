<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

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
            $table->string('sort', 255)->nullable()->comment('ソート順');
            $table->string('plugin_name', 255)->comment('プラグイン英名');
            $table->text('plugin_title')->nullable()->comment('プラグイン名');
            $table->text('plugin_desc')->nullable()->comment('プラグイン詳細');
            $table->string('method_name', 255)->nullable()->comment('機能英名');
            $table->text('method_title')->nullable()->comment('機能名');
            $table->text('method_desc')->nullable()->comment('機能概要');
            $table->text('method_detail')->nullable()->comment('機能詳細');
            $table->string('html_path', 255)->nullable()->comment('HTMLパス');
            $table->text('img_args')->nullable()->comment('画像指定');
            $table->text('test_result')->nullable()->comment('テスト結果');
            $table->nestedSet();
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
