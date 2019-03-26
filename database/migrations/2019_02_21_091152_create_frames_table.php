<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFramesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frames', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id');
            $table->string('frame_title')->nullable();
            $table->string('frame_design')->nullable();
            $table->string('plugin_name');
            $table->integer('frame_col')->nullable();
            $table->string('plug_name')->nullable();
            $table->integer('bucket_id')->nullable();
            $table->integer('display_sequence')->nullable();
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
        Schema::dropIfExists('frames');
    }
}
