<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCounterIdToCounterFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('counter_frames', function (Blueprint $table) {
            $table->dropColumn('counter_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('counter_frames', function (Blueprint $table) {
            $table->integer('counter_id')->comment('カウンターID')->after('id');
        });
    }
}
