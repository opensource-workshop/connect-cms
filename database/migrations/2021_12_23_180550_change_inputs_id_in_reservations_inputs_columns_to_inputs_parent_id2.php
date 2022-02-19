<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInputsIdInReservationsInputsColumnsToInputsParentId2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            // コメント変更
            $table->integer('inputs_parent_id')->comment('予約入力親ID')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            $table->integer('inputs_parent_id')->comment('予約入力ID（外部キー）')->change();
        });
    }
}
