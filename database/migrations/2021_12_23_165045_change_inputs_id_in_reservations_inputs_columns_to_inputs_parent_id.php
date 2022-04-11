<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInputsIdInReservationsInputsColumnsToInputsParentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_inputs_columns', function (Blueprint $table) {
            // コメント変更.
            // move: renameとコメント変更を同時にすると、コメント変更されなかったため、別々のマイグレーションに別ける
            // $table->integer('inputs_id')->comment('予約入力親ID')->change();

            $table->renameColumn('inputs_id', 'inputs_parent_id');
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
            $table->renameColumn('inputs_parent_id', 'inputs_id');
        });
    }
}
