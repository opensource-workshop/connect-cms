<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kalnoy\Nestedset\NestedSet;

class CreateBbsPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbs_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bbs_id')->comment('掲示板ID');
            $table->string('title')->comment('タイトル');
            $table->text('body')->nullable()->comment('本文');
            $table->integer('thread_root_id')->default('0')->comment('根記事ID');
            $table->timestamp('thread_updated_at')->nullable()->comment('スレッド更新日時');
            $table->integer('status')->comment('状態')->default(0);
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
        Schema::dropIfExists('bbs_posts');
    }
}
