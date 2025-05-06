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

    // 定数メンバ
    const read_more_button_default = '続きを読む';
    const close_more_button_default = '閉じる';

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'bucket_id',
        'content_text',
        'content2_text',
        'read_more_flag',
        'read_more_button',
        'close_more_button',
        'status',
    ];

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
