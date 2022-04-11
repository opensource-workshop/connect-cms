<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartTimeEndTimeDayOfWeeksFacilityManagerNameSupplementFromReservationsFacilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_facilities', function (Blueprint $table) {
            $table->integer('is_time_control')->default(0)->comment('利用時間で制御する')->after('hide_flag');
            $table->time('start_time')->nullable()->comment('利用開始時間')->after('is_time_control');
            $table->time('end_time')->nullable()->comment('利用終了時間')->after('start_time');
            $table->string('day_of_weeks')->comment('利用曜日')->after('end_time');
            $table->string('facility_manager_name')->nullable()->comment('施設管理者')->after('is_allow_duplicate');
            $table->text('supplement')->nullable()->comment('補足')->after('facility_manager_name');
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
            $table->dropColumn('is_time_control');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            $table->dropColumn('day_of_weeks');
            $table->dropColumn('facility_manager_name');
            $table->dropColumn('supplement');
        });
    }
}
