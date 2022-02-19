<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Common\Frame;
use App\Models\Core\FrameConfig;
use App\Models\User\Reservations\Reservation;

use App\Enums\PluginName;
use App\Enums\ReservationFrameConfig;

class MigrateCalendarInitialDisplayTypeFromReservationsToFrameConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 施設予約のフレームで、バケツIDセットしてあるもの取得
        $frames = Frame::where('plugin_name', PluginName::getPluginName(PluginName::reservations))
            ->whereNotNull('bucket_id')
            ->get();

        foreach ($frames as $frame) {
            $reservation = Reservation::where('bucket_id', $frame->bucket_id)->first();
            if (!$reservation) {
                // もしもreservation無ければ飛ばす
                continue;
            }

            FrameConfig::updateOrCreate(
                ['frame_id' => $frame->id, 'name' => ReservationFrameConfig::calendar_initial_display_type],
                ['value' => $reservation->calendar_initial_display_type]
            );
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
