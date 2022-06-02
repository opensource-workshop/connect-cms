<?php

namespace App\Models\User\Slideshows;

use Illuminate\Database\Eloquent\Model;

class Slideshows extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'slideshows_name',
        'control_display_flag',
        'indicators_display_flag',
        'fade_use_flag',
        'image_interval',
        'height',
    ];
}
