<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInputsRepeatIdFromReservationsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_inputs', function (Blueprint $table) {
            $table->integer('inputs_parent_id')->nullable()->comment('予約入力親ID')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_inputs', function (Blueprint $table) {
            $table->dropColumn('inputs_parent_id');
        });
    }
}
