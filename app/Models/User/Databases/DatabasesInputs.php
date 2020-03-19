<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class DatabasesInputs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
}
