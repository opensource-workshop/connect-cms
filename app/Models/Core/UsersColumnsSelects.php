<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class UsersColumnsSelects extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'users_columns_id',
        'value',
        'display_sequence',
    ];
}
