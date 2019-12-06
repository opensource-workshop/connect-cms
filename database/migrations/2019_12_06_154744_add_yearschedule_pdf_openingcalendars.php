<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddYearschedulePdfOpeningcalendars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('openingcalendars', function (Blueprint $table) {
            //
            $table->integer('yearschedule_uploads_id')->nullable()->after('view_after_month');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('openingcalendars', function (Blueprint $table) {
            //
            $table->dropColumn('yearschedule_uploads_id');
        });
    }
}
