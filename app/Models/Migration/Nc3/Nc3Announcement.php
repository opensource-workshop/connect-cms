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

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = ['created', 'modified'];
}
