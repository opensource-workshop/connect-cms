<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisplaySequenceReservationsColumnsSelectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_columns_selects', function (Blueprint $table) {
            $table->integer('display_sequence')->comment('表示順')->after('select_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_columns_selects', function (Blueprint $table) {
            $table->dropColumn('display_sequence');
        });
    }
}
