<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogicalDeletionFromReservationsFacilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_facilities', function (Blueprint $table) {
            $table->integer('deleted_id')->nullable()->after('updated_at');
            $table->string('deleted_name', 255)->nullable()->after('deleted_id');
            $table->timestamp('deleted_at')->nullable()->after('deleted_name');
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
            $table->dropColumn('deleted_id');
            $table->dropColumn('deleted_name');
            $table->dropColumn('deleted_at');
        });
    }
}
