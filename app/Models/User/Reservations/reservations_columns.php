<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class reservations_columns extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
}
