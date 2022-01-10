<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDeletedAtFromReservations extends Migration
{
    /**
     * Run the migrations.
     * フォーム, データベースからの流れを組んで施設予約は作成されてる。
     * フォーム, データベースの バケツ系 テーブルは論理削除していないため、一旦のバケツ系テーブル reservations の deleted_at はdropする。
     * もし必要になったら、再度追加する。
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
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
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('calendar_initial_display_type');
        });
    }
}
