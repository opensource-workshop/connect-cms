<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortDownloadCountToDatabasesColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->integer('sort_download_count')->default(0)->comment('ダウンロード件数で並び替え')->after('show_download_count');
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
            $table->dropColumn('sort_download_count');
        });
    }
}
