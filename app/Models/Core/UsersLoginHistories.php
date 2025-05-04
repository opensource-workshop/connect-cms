<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class UsersLoginHistories extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    // 更新する項目の定義
    protected $fillable = [
        'users_id',
        'userid',
        'logged_in_at',
        'ip_address',
        'user_agent',
    ];
}
