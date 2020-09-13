<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUseFilterFlagFilterSearchKeywordFilterSearchColumnsDatabasesFrames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_frames', function (Blueprint $table) {
            $table->integer('use_filter_flag')->comment('絞り込み制御する')->after('default_hide');
            $table->string('filter_search_keyword', 255)->nullable()->comment('絞り込み検索キーワード')->after('use_filter_flag');
            $table->text('filter_search_columns')->nullable()->comment('絞り込み条件')->after('filter_search_keyword');
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
            $table->dropColumn('use_filter_flag');
            $table->dropColumn('filter_search_keyword');
            $table->dropColumn('filter_search_columns');
        });
    }
}
