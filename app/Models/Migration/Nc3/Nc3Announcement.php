<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3Announcement extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'announcements';

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'created' => 'datetime',
        'modified' => 'datetime',
    ];
}
