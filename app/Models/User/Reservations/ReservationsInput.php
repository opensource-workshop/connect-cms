<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;

class ReservationsInput extends Model
{
    protected $dates = ['start_datetime', 'end_datetime'];
}
