<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User\Reservations\ReservationsInput;

class SetInputsParentIdFromReservationsInputs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 予約入力の親IDセット
        if (ReservationsInput::whereNull('inputs_parent_id')->count() > 0) {
            $inputs = ReservationsInput::get();
            foreach ($inputs as $input) {
                ReservationsInput::where('id', $input->id)->update(['inputs_parent_id' => $input->id]);
            }
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
