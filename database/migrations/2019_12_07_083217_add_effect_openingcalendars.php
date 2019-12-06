<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEffectOpeningcalendars extends Migration
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
            $table->integer('smooth_scroll')->default('0')->after('yearschedule_uploads_id');
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
            $table->dropColumn('smooth_scroll');
        });
    }
}
