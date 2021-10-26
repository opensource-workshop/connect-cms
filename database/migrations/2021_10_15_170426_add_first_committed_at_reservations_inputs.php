<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFirstCommittedAtReservationsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_inputs', function (Blueprint $table) {
            $table->timestamp('first_committed_at')->nullable()->comment('初回確定日時')->after('end_datetime');
        });

        // 初期データ設定：初回確定日時が入っていないと、次回の更新時に新規とみなして、設定によっては登録通知メールが送られるため、初期値を設定
        DB::statement('UPDATE reservations_inputs input SET input.first_committed_at = input.created_at');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_inputs', function (Blueprint $table) {
            $table->dropColumn('first_committed_at');
        });
    }
}
