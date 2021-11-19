<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsFacility;

use App\Enums\NotShowType;

class ChnageReservationsHideFlagFromNullTo0 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 表示フラグはnullではなく、明示的に値を設定して、表示・非表示をはっきりさせる。nullでは未設定と判別つかないため。
        // hide_flag = null を hide_flag = 0 に移行。

        // reservations_columns.hide_flag
        // reservations_columns_selects.hide_flag
        // reservations_facilities.hide_flag

        // 予約カラム
        if (ReservationsColumn::whereNull('hide_flag')->count() > 0) {
            ReservationsColumn::whereNull('hide_flag')->update(['hide_flag' => NotShowType::show]);
        }

        // 予約カラム選択肢
        if (ReservationsColumnsSelect::whereNull('hide_flag')->count() > 0) {
            ReservationsColumnsSelect::whereNull('hide_flag')->update(['hide_flag' => NotShowType::show]);
        }

        // 施設
        if (ReservationsFacility::whereNull('hide_flag')->count() > 0) {
            ReservationsFacility::whereNull('hide_flag')->update(['hide_flag' => NotShowType::show]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
