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
    protected $target_dates;
    protected $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $facility_id, array $target_dates, ?string $message = null)
    {
        $this->facility_id = $facility_id;
        $this->target_dates = $target_dates;
        $this->message = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $facility = ReservationsFacility::find($this->facility_id);

        foreach ($this->target_dates as $target_date) {

            if (empty($target_date)) {
                // 値がなければチェックしない
                continue;
            }
            $target_date = new Carbon($target_date);

            $day_of_weeks = explode('|', $facility->day_of_weeks);

            if (in_array((string)$target_date->dayOfWeek, $day_of_weeks, true)) {
                // 対象日の曜日が含まれるため、正常. なにもしない
            } else {
                // 対象日の曜日が含まれないため、エラー
                return false;
            }
        }

        // 正常
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?? '利用できる曜日で入力してください。';
    }
}
