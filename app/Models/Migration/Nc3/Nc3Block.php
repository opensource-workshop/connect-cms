<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3Block extends Model
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
    protected $table = 'blocks';

    // 公開状態
    const
        public_type_open = '1',     // 公開
        public_type_close = '0',    // 非公開
        public_type_limited = '2';  // 期限付き公開
}
