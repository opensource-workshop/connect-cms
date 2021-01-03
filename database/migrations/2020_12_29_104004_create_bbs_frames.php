<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBbsFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbs_frames', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bbs_id')->comment('掲示板ID');
            $table->integer('frame_id')->comment('フレームID');
            $table->integer('view_format')->nullable()->comment('表示形式');
            $table->integer('thread_sort_flag')->nullable()->comment('根記事の表示順');
            $table->integer('view_count')->nullable()->comment('1ページの表示件数');
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
        Schema::dropIfExists('bbs_frames');
    }
}
