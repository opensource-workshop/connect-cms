<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User\Reservations\ReservationsInput;

class MigrateFirstCommittedAtIsNotNullFromReservationsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 予約
        $inputs = ReservationsInput::whereNull('first_committed_at')->get();
        foreach ($inputs as $input) {
            $input->first_committed_at = $input->created_at;
            $input->update();
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
