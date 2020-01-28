<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLinktextOpeningcalendars extends Migration
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
            $table->string('yearschedule_link_text')->nullable()->after('yearschedule_uploads_id');
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
            $table->dropColumn('yearschedule_link_text');
        });
    }
}
