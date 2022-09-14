<?php

namespace App\Models\User\Opacs;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class OpacsConfigsSelect extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'opacs_id',
        'name',
        'value',
        'display_sequence',
    ];
}
