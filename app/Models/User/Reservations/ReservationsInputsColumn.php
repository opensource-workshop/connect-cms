<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class ReservationsInputsColumn extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'reservations_id',
        'inputs_parent_id',
        'column_id',
        'value',
    ];
}
