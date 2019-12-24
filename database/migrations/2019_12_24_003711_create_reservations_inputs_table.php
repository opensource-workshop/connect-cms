<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_inputs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）');
            $table->integer('facility_id')->comment('施設ID（外部キー）');
            $table->dateTime('start_datetime')->comment('予約開始日時');
            $table->dateTime('end_datetime')->comment('予約終了日時');
            $table->string('input_user_id')->comment('登録者ID');
            $table->string('update_user_id')->comment('更新者ID');
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
        Schema::dropIfExists('reservations_inputs');
    }
}
