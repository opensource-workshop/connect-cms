<?php

namespace App\Models\User\Opacs;

use Illuminate\Database\Eloquent\Model;

class OpacsBooksLents extends Model
{
    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['lent_at', 'scheduled_return'];
}
