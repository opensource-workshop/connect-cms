<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLimitedByRoleFromReservationsFacilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_facilities', function (Blueprint $table) {
            $table->integer('is_limited_by_role')->default(0)->comment('権限で予約制限する')->after('is_allow_duplicate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_facilities', function (Blueprint $table) {
            $table->dropColumn('is_limited_by_role');
        });
    }
}
