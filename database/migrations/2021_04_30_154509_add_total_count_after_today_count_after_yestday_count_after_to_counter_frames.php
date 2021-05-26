<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCountAfterTodayCountAfterYestdayCountAfterToCounterFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('counter_frames', function (Blueprint $table) {
            $table->string('total_count_after', 255)->nullable()->comment('累計カウントの単位')->after('yestday_count_title');
            $table->string('today_count_after', 255)->nullable()->comment('本日のカウントの単位')->after('total_count_after');
            $table->string('yestday_count_after', 255)->nullable()->comment('昨日のカウントの単位')->after('today_count_after');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('counter_frames', function (Blueprint $table) {
            $table->dropColumn('total_count_after');
            $table->dropColumn('today_count_after');
            $table->dropColumn('yestday_count_after');
        });
    }
}
