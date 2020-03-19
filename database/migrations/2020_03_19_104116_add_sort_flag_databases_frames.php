<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSortFlagDatabasesFrames extends Migration
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
            $table->string('use_sort_flag')->comment('並べ替え項目の設定')->nullable()->after('use_select_flag');
            $table->string('default_sort_flag')->comment('並べ替え初期値の設定')->nullable()->after('use_sort_flag');
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
            $table->dropColumn('use_sort_flag');
            $table->dropColumn('default_sort_flag');
        });
    }
}
