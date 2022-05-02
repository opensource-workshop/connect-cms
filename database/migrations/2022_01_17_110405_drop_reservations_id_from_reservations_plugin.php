<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropReservationsIdFromReservationsPlugin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_facilities', function (Blueprint $table) {
            $table->dropColumn('reservations_id');
        });

        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->dropColumn('reservations_id');
        });

        Schema::table('reservations_columns_selects', function (Blueprint $table) {
            $table->dropColumn('reservations_id');
        });

        Schema::table('reservations_inputs', function (Blueprint $table) {
            $table->dropColumn('reservations_id');
        });
        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            $table->dropColumn('reservations_id');
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
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）')->after('id');
        });

        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）')->after('id');
        });

        Schema::table('reservations_columns_selects', function (Blueprint $table) {
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）')->after('id');
        });

        Schema::table('reservations_inputs', function (Blueprint $table) {
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）')->after('inputs_parent_id');
        });

        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            $table->integer('reservations_id')->comment('施設予約ID（外部キー）')->after('id');
        });
    }
}
