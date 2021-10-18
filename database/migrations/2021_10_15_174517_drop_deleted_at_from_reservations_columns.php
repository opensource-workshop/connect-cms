<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDeletedAtFromReservationsColumns extends Migration
{
    /**
     * Run the migrations.
     * フォーム, データベースからの流れを組んで reservations_columns テーブルは作成されてる。
     * フォーム, データベースの xxxx_columns テーブルは論理削除していないため、施設予約の deleted_at はdropする。
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservations_columns', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('display_sequence');
        });
    }
}
