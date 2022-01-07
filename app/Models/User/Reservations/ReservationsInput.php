<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;

use App\Userable;

use App\Enums\DayOfWeek;

class ReservationsInput extends Model
{
    // 保存時のユーザー関連データの保持
    // 履歴保持として使わず、繰り返し予定修正時の delete->insert する時に created_idを保持するために使う。
    use Userable;

    // 更新する項目の定義
    protected $fillable = [
        'inputs_parent_id',
        'reservations_id',
        'facility_id',
        'start_datetime',
        'end_datetime',
        'first_committed_at',
        'status',
    ];

    protected $dates = ['start_datetime', 'end_datetime'];

    /**
     * 表示する予約日付
     * start_datetime は not nullのため、空にならない想定
     */
    public function displayDate()
    {
        $display = $this->start_datetime->format(__('messages.format_date'));
        $display .= ' (' . DayOfWeek::getDescription($this->start_datetime->dayOfWeek) . ')';

        return $display;
    }
}
