<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCounterFramesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('counter_frames', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('counter_id')->comment('カウンターID');
            $table->integer('frame_id')->comment('フレームID');
            $table->string('design_type', 255)->nullable()->comment('表示形式');
            $table->integer('use_total_count')->default(1)->comment('累計カウントの表示');
            $table->integer('use_today_count')->default(1)->comment('本日のカウント表示');
            $table->integer('use_yestday_count')->default(1)->comment('昨日のカウント表示');
            $table->string('total_count_title', 255)->nullable()->comment('累計カウントの項目名');
            $table->string('today_count_title', 255)->nullable()->comment('本日のカウントの項目名');
            $table->string('yestday_count_title', 255)->nullable()->comment('昨日のカウントの項目名');
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
        Schema::dropIfExists('counter_frames');
    }
}
