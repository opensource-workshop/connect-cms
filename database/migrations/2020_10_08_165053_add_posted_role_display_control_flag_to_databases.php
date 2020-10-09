<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostedRoleDisplayControlFlagToDatabases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('databases', function (Blueprint $table) {
            $table->integer('posted_role_display_control_flag')->default(0)->comment('表示順-権限で表示カラムを制御')->after('databases_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('databases', function (Blueprint $table) {
            $table->dropColumn('posted_role_display_control_flag');
        });
    }
}
