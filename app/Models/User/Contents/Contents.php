<?php

namespace App\Models\User\Contents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class Contents extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'bucket_id',
        'content_text',
        'content2_text',
        'view_more',
        'hide_more',
        'status',
    ];

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['deleted_at'];
}
