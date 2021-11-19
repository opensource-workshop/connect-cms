<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class ReservationsFacility extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'reservations_id',
        'facility_name',
        'hide_flag',
        'reservations_categories_id',
        'columns_set_id',
        'display_sequence',
    ];
}
