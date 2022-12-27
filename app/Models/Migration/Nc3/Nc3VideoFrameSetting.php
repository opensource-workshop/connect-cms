<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3VideoFrameSetting extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'video_frame_settings';

    // 表示順
    const display_order_new  = 'new',           // 新着順
        display_order_title  = 'title',         // タイトル順
        display_order_play = 'play',            // 再生数順
        display_order_like  = 'like';           // 評価順
}
