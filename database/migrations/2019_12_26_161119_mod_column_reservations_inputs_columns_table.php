<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModColumnReservationsInputsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            DB::statement('ALTER TABLE `reservations_inputs_columns` MODIFY COLUMN value varchar(255)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            DB::statement('ALTER TABLE `reservations_inputs_columns` MODIFY COLUMN value varchar(255) NOT NULL');
        });
    }
}
