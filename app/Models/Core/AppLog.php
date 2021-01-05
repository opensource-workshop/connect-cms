<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class AppLog extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['ip_address', 'plugin_name', 'url', 'route_name', 'method', 'type', 'return_code', 'value'];
}
