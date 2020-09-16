<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class FormsInputCols extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['forms_inputs_id', 'forms_columns_id', 'value'];
}
