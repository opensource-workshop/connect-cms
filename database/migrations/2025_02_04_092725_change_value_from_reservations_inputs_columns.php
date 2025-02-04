<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeValueFromReservationsInputsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            $table->text('value')->nullable()->comment('入力値')->change();
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
            $table->string('value', 255)->nullable()->comment('入力値')->change();
        });
    }
}
