<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReservationsColumnsSetId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_facilities', function (Blueprint $table) {
            $table->integer('columns_set_id')->comment('項目セットID')->after('reservations_categories_id');
        });

        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->integer('columns_set_id')->comment('項目セットID')->after('reservations_id');
        });

        Schema::table('reservations_columns_selects', function (Blueprint $table) {
            $table->integer('columns_set_id')->comment('項目セットID')->after('reservations_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_facilities', function (Blueprint $table) {
            $table->dropColumn('columns_set_id');
        });

        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->dropColumn('columns_set_id');
        });

        Schema::table('reservations_columns_selects', function (Blueprint $table) {
            $table->dropColumn('columns_set_id');
        });
    }
}
