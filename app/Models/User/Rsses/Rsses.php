<?php

namespace App\Models\User\Rsses;

use Illuminate\Database\Eloquent\Model;

class Rsses extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'rsses_name',
        'cache_interval',
        'mergesort_flag',
        'mergesort_count',
    ];
}
