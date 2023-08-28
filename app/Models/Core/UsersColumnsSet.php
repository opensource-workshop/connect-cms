<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;
use App\Traits\ConnectModelDisplaySequenceTrait;

class UsersColumnsSet extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use ConnectModelDisplaySequenceTrait;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'name',
        'use_variable',
        'variable_name',
        'display_sequence',
    ];
}
