<?php

namespace App\Models\User\Faqs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class FaqsPostsTags extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'posted_at' => 'datetime',
    ];
}
