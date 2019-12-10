<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_facilities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）');
            $table->string('facility_name')->comment('施設名');
            $table->string('hide_flag')->comment('非表示フラグ(1:非表示)')->nullable();
            $table->integer('display_sequence')->comment('表示順');
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
        Schema::dropIfExists('reservations_facilities');
    }
}
