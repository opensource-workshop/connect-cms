<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleFlagFromReservationsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->integer('title_flag')->default(0)->comment('新着等のタイトル指定')->after('hide_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->dropColumn('title_flag');
        });
    }
}
