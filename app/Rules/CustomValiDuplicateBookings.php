<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Support\Collection;

use App\Models\User\Reservations\ReservationsInput;

/**
 * 施設予約重複チェック
 */
class CustomValiDuplicateBookings implements Rule
{
    protected $facility_id;
    protected $input_ids;
    protected $start_datetime;
    protected $end_datetime;
    protected $message;

    protected $error_date;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $facility_id, Collection $input_ids, string $start_datetime, string $end_datetime, ?string $message = null)
    {
        $this->facility_id = $facility_id;
        $this->input_ids = $input_ids;

        $this->start_datetime = $start_datetime;
        $this->end_datetime = $end_datetime;

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
        // debug:確認したいSQLの前にこれを仕込んで
        // \DB::enableQueryLog();

        // 例）
        // この予定のみ
        // $input_cols = ReservationsInput::whereNotIn('id', [id2])
        // この予定以降
        // $input_cols = ReservationsInput::whereNotIn('id', [id3, id4])
        // 全て
        // $input_cols = ReservationsInput::whereNotIn('id', [id1, id2, id3, id4])

        // 重複予約あるか
        $input_cols = ReservationsInput::whereNotIn('id', $this->input_ids)
            ->where('facility_id', $this->facility_id)
            ->Where(function ($query) {
                // 例)
                // 予約済み：   10/12 (S)14:00-(E)15:00
                // 予約する時間：10/12 13:00-14:00

                // 予約する時間が、予約済みの開始時間にまたがってないか
                // 例) 13:00 <= (S)14:00 < 14:00
                $query->orWhere(function ($tmp_query) {
                    $tmp_query->where('start_datetime', '>=', $this->start_datetime)
                        ->where('start_datetime', '<', $this->end_datetime);
                });
                // 予約する時間が、予約済みの終了時間にまたがってないか
                // 例) 13:00 < (E)15:00 <= 14:00
                $query->orWhere(function ($tmp_query) {
                    $tmp_query->where('end_datetime', '>', $this->start_datetime)
                        ->where('end_datetime', '<=', $this->end_datetime);
                });
                // 予約開始時間が、予約済みの時間内から始まってないか
                // 例) (S)14:00 <= 13:00 < (E)15:00
                $query->orWhere(function ($tmp_query) {
                    $tmp_query->where('start_datetime', '<=', $this->start_datetime)
                        ->where('end_datetime', '>', $this->start_datetime);
                });
            })
            ->first();

        // debug: sql dumpする
        // \Log::debug(var_export(\DB::getQueryLog(), true));

        if ($input_cols) {
            // 値ありは重複
            // $this->error_date = $input_cols->start_datetime->format('Y-m-d') . ' ' . $input_cols->start_datetime->format('H:i') . '～' . $input_cols->end_datetime->format('H:i');
            $this->error_date = $input_cols->start_datetime->format('Y-m-d');
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ? $this->message . "（{$this->error_date}）" : '既に予約が入っているため、予約できません。（'. $this->error_date . '）';
    }
}
