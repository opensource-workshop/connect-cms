<?php

namespace App\Models\User\Openingcalendars;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Openingcalendars extends Model
{
    // 論理削除
    use SoftDeletes;
}
