<?php

namespace App\Models\User\Openingcalendars;

use Illuminate\Database\Eloquent\Model;

class OpeningcalendarsDays extends Model
{
    //
    protected $fillable = ['openingcalendars_id', 'opening_date', 'openingcalendars_patterns_id'];
}
