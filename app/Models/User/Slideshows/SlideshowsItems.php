<?php

namespace App\Models\User\Slideshows;

use Illuminate\Database\Eloquent\Model;

class SlideshowsItems extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'slideshows_id',
        'image_path',
        'uploads_id',
        'link_url',
        'link_target',
        'caption',
        'display_flag',
        'display_sequence',
    ];
}
