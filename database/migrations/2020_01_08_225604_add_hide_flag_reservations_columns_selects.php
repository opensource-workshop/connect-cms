<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHideFlagReservationsColumnsSelects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_columns_selects', function (Blueprint $table) {
            $table->string('hide_flag')->comment('非表示フラグ(1:非表示)')->nullable()->after('select_name');
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
            $table->dropColumn('hide_flag');
        });
    }
}
