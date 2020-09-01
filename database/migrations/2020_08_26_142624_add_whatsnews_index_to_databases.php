<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 新着情報に係るインデックスをデータベースプラグイン関係のテーブルに追加
 */
class AddWhatsnewsIndexToDatabases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->index(['databases_id'], 'databases_id_index');
        });

        Schema::table('databases_columns', function (Blueprint $table) {
            $table->index(['databases_id', 'title_flag'], 'databases_id_and_title_flag_index');
        });

        Schema::table('databases_input_cols', function (Blueprint $table) {
            $table->index(['databases_inputs_id', 'databases_columns_id'], 'databases_inputs_id_and_databases_columns_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases_inputs', function (Blueprint $table) {
            $table->dropIndex('databases_id_index');
        });

        Schema::table('databases_columns', function (Blueprint $table) {
            $table->dropIndex('databases_id_and_title_flag_index');
        });

        Schema::table('databases_input_cols', function (Blueprint $table) {
            $table->dropIndex('databases_inputs_id_and_databases_columns_id_index');
        });
    }
}
