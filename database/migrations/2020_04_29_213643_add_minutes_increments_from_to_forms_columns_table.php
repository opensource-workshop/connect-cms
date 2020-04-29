<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinutesIncrementsFromToFormsColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->integer('minutes_increments_from')->default(10)->comment('分刻み指定（From用）')->after('minutes_increments');
            $table->integer('minutes_increments_to')->default(10)->comment('分刻み指定（To用）')->after('minutes_increments_from');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms_columns', function (Blueprint $table) {
            $table->dropColumn('minutes_increments_from');
            $table->dropColumn('minutes_increments_to');
        });
    }
}
