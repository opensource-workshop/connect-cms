<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUseSortFlagDatabasesFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_frames', function (Blueprint $table) {
            //
            $table->dropColumn('use_sort_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_frames', function (Blueprint $table) {
            //
            $table->integer('use_sort_flag')->comment('並べ替え使用の有無')->default(1)->after('use_select_flag');
        });
    }
}
