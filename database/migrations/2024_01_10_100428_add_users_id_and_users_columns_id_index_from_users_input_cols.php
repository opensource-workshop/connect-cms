<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersIdAndUsersColumnsIdIndexFromUsersInputCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_input_cols', function (Blueprint $table) {
            $table->index(['users_id', 'users_columns_id'], 'users_id_and_users_columns_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_input_cols', function (Blueprint $table) {
            $table->dropIndex('users_id_and_users_columns_id_index');
        });
    }
}
