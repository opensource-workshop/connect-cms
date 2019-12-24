<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsInputsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_inputs_columns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）');
            $table->integer('inputs_id')->comment('予約入力ID（外部キー）');
            $table->integer('column_id')->comment('項目ID（外部キー）');
            $table->string('value')->comment('入力値');
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
        Schema::dropIfExists('reservations_inputs_columns');
    }
}
