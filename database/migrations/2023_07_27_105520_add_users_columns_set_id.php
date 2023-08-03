<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersColumnsSetId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @see database\migrations\2021_11_18_175953_add_reservations_columns_set_id.php is copy
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('columns_set_id')->comment('項目セットID')->after('status');
        });

        Schema::table('users_columns', function (Blueprint $table) {
            $table->integer('columns_set_id')->comment('項目セットID')->after('id');
        });

        Schema::table('users_columns_selects', function (Blueprint $table) {
            $table->integer('columns_set_id')->comment('項目セットID')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('columns_set_id');
        });

        Schema::table('users_columns', function (Blueprint $table) {
            $table->dropColumn('columns_set_id');
        });

        Schema::table('users_columns_selects', function (Blueprint $table) {
            $table->dropColumn('columns_set_id');
        });
    }
}
