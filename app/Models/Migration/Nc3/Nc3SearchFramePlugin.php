<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3SearchFramePlugin extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'search_frames_plugins';

    /**
     * タイムスタンプ管理
     */
    public $timestamps = false;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'frame_key',
        'plugin_key',
        'created',
        'modified',
        'created_user',
        'modified_user',
    ];
}
