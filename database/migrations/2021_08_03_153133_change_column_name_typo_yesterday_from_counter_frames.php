<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnNameTypoYesterdayFromCounterFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('counter_frames', function (Blueprint $table) {
            $table->renameColumn('use_yestday_count', 'use_yesterday_count');
            $table->renameColumn('yestday_count_title', 'yesterday_count_title');
            $table->renameColumn('yestday_count_after', 'yesterday_count_after');
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
            $table->renameColumn('use_yesterday_count', 'use_yestday_count');
            $table->renameColumn('yesterday_count_title', 'yestday_count_title');
            $table->renameColumn('yesterday_count_after', 'yestday_count_after');
        });
    }
}
