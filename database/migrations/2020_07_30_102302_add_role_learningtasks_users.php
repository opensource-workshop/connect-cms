<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleLearningtasksUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learningtasks_users', function (Blueprint $table) {
            //
            $table->string('role_name', 255)->nullable()->after('user_id')->comment('役割の定義名');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learningtasks_users', function (Blueprint $table) {
            //
            $table->dropColumn('role_name');
        });
    }
}
