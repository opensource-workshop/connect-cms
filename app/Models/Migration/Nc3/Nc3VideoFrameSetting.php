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

    /**
     * タイムスタンプ管理
     */
    public $timestamps = false;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'frame_key',
        'display_order',
        'created',
        'modified',
        'created_user',
        'modified_user',
    ];

    // 表示順
    const display_order_new  = 'new',           // 新着順
        display_order_title  = 'title',         // タイトル順
        display_order_play = 'play',            // 再生数順
        display_order_like  = 'like';           // 評価順
}
