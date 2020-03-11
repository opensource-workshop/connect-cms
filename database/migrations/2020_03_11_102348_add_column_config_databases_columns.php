<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnConfigDatabasesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            //
            $table->integer('list_hide_flag')->comment('一覧から非表示にする指定')->default(0)->after('rule_date_after_equal');
            $table->integer('detail_hide_flag')->comment('詳細から非表示にする指定')->default(0)->after('list_hide_flag');
            $table->integer('sort_flag')->comment('並べ替え指定')->default(0)->after('detail_hide_flag');
            $table->integer('search_flag')->comment('検索対象指定')->default(0)->after('sort_flag');
            $table->integer('select_flag')->comment('絞り込み対象指定')->default(0)->after('search_flag');
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
            //
            $table->dropColumn('list_hide_flag');
            $table->dropColumn('detail_hide_flag');
            $table->dropColumn('sort_flag');
            $table->dropColumn('search_flag');
            $table->dropColumn('select_flag');
        });
    }
}
