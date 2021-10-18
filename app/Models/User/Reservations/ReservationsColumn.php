<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class ReservationsColumn extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'reservations_id',
        'column_type',
        'column_name',
        'required',
        'hide_flag',
        'display_sequence',
    ];
}
