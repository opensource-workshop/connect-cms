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
        'facility_id',
        'start_datetime',
        'end_datetime',
        'first_committed_at',
        'status',
    ];

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

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

    /**
     * 表示する予約終了時間
     * end_datetime は not nullのため、空にならない想定
     */
    public function displayEndtime()
    {
        $endtime = $this->end_datetime->format('H:i');
        if ($endtime == '00:00') {
            $endtime = '24:00';
        }
        return $endtime;
    }

    /**
     * 利用日時のFrom～To 取得
     */
    public function getStartEndDatetimeStr(): string
    {
        $endtime = $this->end_datetime->format('H時i分');
        if ($endtime == '00時00分') {
            $endtime = '24時00分';
        }

        return $this->start_datetime->format('Y年m月d日 H時i分') . ' ～ ' . $endtime;
    }
}
