<?php

namespace App\Models\User\Reservations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\App;

use App\UserableNohistory;

use App\Enums\ConnectLocale;
use App\Enums\DayOfWeek;

class ReservationsFacility extends Model
{
    // 平日
    const weekday = DayOfWeek::mon.'|'.DayOfWeek::tue.'|'.DayOfWeek::wed.'|'.DayOfWeek::thu.'|'.DayOfWeek::fri;
    // 全日
    const all_days = DayOfWeek::sun.'|'.DayOfWeek::mon.'|'.DayOfWeek::tue.'|'.DayOfWeek::wed.'|'.DayOfWeek::thu.'|'.DayOfWeek::fri.'|'.DayOfWeek::sat;

    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'reservations_id',
        'facility_name',
        'hide_flag',
        'is_time_control',
        'start_time',
        'end_time',
        'day_of_weeks',
        'reservations_categories_id',
        'columns_set_id',
        'is_allow_duplicate',
        'facility_manager_name',
        'supplement',
        'display_sequence',
    ];

    /**
     * 施設更新処理
     */
    public function getDayOfWeeksDisplay()
    {
        $locate = App::getLocale();

        if ($this->day_of_weeks == self::weekday) {
            return __('messages.weekday');
        } elseif ($this->day_of_weeks == self::all_days) {
            return __('messages.all_days');
        }

        $display = '';
        $day_of_weeks = explode('|', $this->day_of_weeks);

        foreach ($day_of_weeks as $day_of_week) {
            if ($locate == ConnectLocale::en) {
                $display .= DayOfWeek::enum_en[$day_of_week] . ',';
            } else {
                $display .= DayOfWeek::enum_ja[$day_of_week] . ',';
            }
        }

        return rtrim($display, ',');
    }
}
