<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlayCountToDatabasesColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->integer('show_play_count')->default(0)->comment('再生回数を表示する')->after('show_download_count');
            $table->integer('sort_play_count')->default(0)->comment('再生回数で並び替え')->after('sort_download_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->dropColumn('show_play_count');
            $table->dropColumn('sort_play_count');
        });
    }
}
