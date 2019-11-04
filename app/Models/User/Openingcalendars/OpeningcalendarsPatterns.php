<?php

namespace App\Models\User\Openingcalendars;

use Illuminate\Database\Eloquent\Model;

class OpeningcalendarsPatterns extends Model
{
    //
    protected $fillable = ['openingcalendars_id', 'display_sequence', 'pattern', 'caption', 'color'];
}
