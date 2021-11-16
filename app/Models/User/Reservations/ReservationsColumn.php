<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;
use App\Enums\ReservationColumnType;

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

    /**
     * 入力しないカラム型か
     */
    public static function isNotInputColumnType($column_type)
    {
        // 登録日型・更新日型等は入力しない
        if ($column_type == ReservationColumnType::created ||
                $column_type == ReservationColumnType::updated ||
                $column_type == ReservationColumnType::created_name ||
                $column_type == ReservationColumnType::updated_name) {
            return true;
        }
        return false;
    }
}
