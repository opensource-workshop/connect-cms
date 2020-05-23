<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClassDatabasesColumns extends Migration
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
            $table->string('classname')->nullable()->comment('Class名')->after('select_flag');
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
            $table->dropColumn('classname');
        });
    }
}
