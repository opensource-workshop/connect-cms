<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class UsersLoginHistories extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = [
        'logged_in_at',
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
