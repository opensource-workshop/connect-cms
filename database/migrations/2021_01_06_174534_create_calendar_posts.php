<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCalendarPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('calendar_id')->comment('カレンダーID');
            $table->integer('allday_flag')->default(0)->comment('全日予定フラグ');
            $table->date('start_date')->comment('開始日');
            $table->time('start_time')->comment('開始時間');
            $table->date('end_date')->comment('終了日');
            $table->time('end_time')->comment('終了時間');
            $table->string('title')->comment('タイトル');
            $table->text('body')->nullable()->comment('本文');
            $table->integer('status')->default(0)->comment('状態');
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
        Schema::dropIfExists('calendar_posts');
    }
}
