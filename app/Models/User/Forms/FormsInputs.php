<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class FormsInputs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['forms_id'];
}
