<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleDisplayControlFlagDatabasesColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases_columns', function (Blueprint $table) {
            $table->integer('role_display_control_flag')->default(0)->comment('権限で表示カラムを制御')->after('detail_hide_flag');
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
            $table->dropColumn('role_display_control_flag');
        });
    }
}
