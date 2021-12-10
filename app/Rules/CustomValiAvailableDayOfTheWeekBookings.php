<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Models\User\Reservations\ReservationsFacility;
use Carbon\Carbon;

/**
 * 施設予約 利用できる曜日チェック
 */
class CustomValiAvailableDayOfTheWeekBookings implements Rule
{
    protected $facility_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($facility_id)
    {
        $this->facility_id = $facility_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value 対象日
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            // 値がなければチェックしない（チェックOKにする）
            return true;
        }
        $target_date = new Carbon($value);

        $facility = ReservationsFacility::find($this->facility_id);
        $day_of_weeks = explode('|', $facility->day_of_weeks);

        if (in_array((string)$target_date->dayOfWeek, $day_of_weeks, true)) {
            // 対象日の曜日が含まれるため、正常
            return true;
        }

        // 対象日の曜日が含まれないため、エラー
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '利用できる曜日で入力してください。';
    }
}
