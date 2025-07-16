<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3CalendarFrameSetting extends Model
{
    use HasFactory;
    
    /**
     * タイムスタンプの自動更新を無効にする
     */
    public $timestamps = false;
    
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'calendar_frame_settings';
}
