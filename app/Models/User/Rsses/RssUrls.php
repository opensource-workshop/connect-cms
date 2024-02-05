<?php

namespace App\Models\User\Rsses;

use Illuminate\Database\Eloquent\Model;

class RssUrls extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'rsses_id',
        'url',
        'title',
        'caption',
        'item_count',
        'display_flag',
        'display_sequence',
        'xml',
        'xml_updated_at',
    ];
}
