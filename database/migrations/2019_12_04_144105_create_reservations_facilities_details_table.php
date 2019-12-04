<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsFacilitiesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_facilities_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reservations_facilities_id')->comment('施設ID（外部キー）');
            $table->string('subject')->comment('予約件名');
            $table->timestamp('start_datetime')->comment('利用開始日時')->nullable();
            $table->timestamp('end_datetime')->comment('利用終了日時')->nullable();
            $table->string('reserving_person_name')->comment('予約者名');
            $table->string('registered_person_id')->comment('予約登録者ID');
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
        Schema::dropIfExists('reservations_facilities_details');
    }
}
