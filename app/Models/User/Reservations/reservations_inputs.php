<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;

class reservations_inputs extends Model
{
    protected $dates = ['start_datetime', 'end_datetime'];
}
