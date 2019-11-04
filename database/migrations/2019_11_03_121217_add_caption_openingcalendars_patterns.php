<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCaptionOpeningcalendarsPatterns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('openingcalendars_patterns', function (Blueprint $table) {
            //
            $table->string('caption', 255)->nullable()->after('openingcalendars_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('openingcalendars_patterns', function (Blueprint $table) {
            //
            $table->dropColumn('created_id');
        });
    }
}
