<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservationsColumnsSelectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations_columns_selects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）');
            $table->integer('column_id')->comment('項目ID');
            $table->string('select_name')->comment('選択肢名');
            $table->softDeletes();
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
        Schema::dropIfExists('reservations_columns_selects');
    }
}
