<?php

namespace App\Models\User\Forms;

use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormsInputs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use HasFactory;
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['forms_id'];
}
