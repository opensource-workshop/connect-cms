<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3Language extends Model
{
    use HasFactory;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'languages';

    /**
     * タイムスタンプの自動更新を無効にする
     */
    public $timestamps = false;

    const
        language_id_en = 1,     // 英語
        language_id_ja = 2;     // 日本語
}
