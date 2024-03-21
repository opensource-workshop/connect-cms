<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseVariableVariableNameFromUsersColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_columns', function (Blueprint $table) {
            $table->integer('use_variable')->default(0)->comment('変数名の使用')->after('column_name');
            $table->string('variable_name')->nullable()->comment('変数名')->after('use_variable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_columns', function (Blueprint $table) {
            $table->dropColumn('use_variable');
            $table->dropColumn('variable_name');
        });
    }
}
