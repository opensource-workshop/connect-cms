<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Models\User\Reservations\ReservationsFacility;

/**
 * 施設予約 利用時間内チェック
 */
class CustomValiAvailableTimeBookings implements Rule
{
    protected $facility_id;

    protected $start_time;
    protected $end_time;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($facility_id, $start_time, $end_time)
    {
        $this->facility_id = $facility_id;

        $this->start_time = $start_time;
        $this->end_time = $end_time;
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
        if (empty($this->start_time) || empty($this->end_time)) {
            // いずれか値がなければチェックしない（チェックOKにする）
            return true;
        }

        // debug:確認したいSQLの前にこれを仕込んで
        // \DB::enableQueryLog();

        // 利用可能時間か
        $facility = ReservationsFacility::where('id', $this->facility_id)
            ->Where(function ($query) {
                // 例)
                // 利用時間：   (S)13:00-(E)15:00
                // 予約する時間：13:00-14:00

                // 予約開始時間は、利用時間内か
                // 例) (S)13:00 <= 13:00 < (E)15:00
                $query->where('start_time', '<=', $this->start_time)
                    ->where('end_time', '>', $this->start_time);

                // 予約終了時間は、利用時間内か
                // 例) (S)13:00 < 14:00 <= (E)15:00
                $query->where('start_time', '<', $this->end_time)
                    ->where('end_time', '>=', $this->end_time);
            })
            ->first();

        // debug: sql dumpする
        // \Log::debug(var_export(\DB::getQueryLog(), true));

        if ($facility) {
            // 値ありは利用時間範囲内
            return true;
        }

        // 値なしは利用時間範囲外でエラー
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '利用時間内で入力してください。';
    }
}
