<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User\Reservations\ReservationsFacility;
use App\Models\User\Reservations\ReservationsInput;

class MigrateEndtime24hFromReservationsInputsAndReservationsFacilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 予約
        $inputs = ReservationsInput::whereTime('end_datetime', '23:55:00')->get();
        foreach ($inputs as $input) {
            // 終了時間 23:55 を +5分で 24h に更新
            $input->end_datetime = $input->end_datetime->addMinutes(5);
            $input->update();
        }

        // 施設
        ReservationsFacility::where('end_time', '23:55:00')->update(['end_time' => '24:00:00']);
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
