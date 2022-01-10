<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeCreatedNameUpdatedNamePositionFromReservationsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // カラムの順番も直す
        DB::statement("ALTER TABLE reservations_inputs MODIFY COLUMN created_name varchar(255) NULL AFTER created_id");
        DB::statement("ALTER TABLE reservations_inputs MODIFY COLUMN updated_name varchar(255) NULL AFTER updated_id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // カラムの順番を元に戻す
        DB::statement("ALTER TABLE reservations_inputs MODIFY COLUMN created_name varchar(255) NULL AFTER end_datetime");
        DB::statement("ALTER TABLE reservations_inputs MODIFY COLUMN updated_name varchar(255) NULL AFTER created_name");
    }
}
