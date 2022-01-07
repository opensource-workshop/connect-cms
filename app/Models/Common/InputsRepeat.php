<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

use App\Models\Common\ConnectCarbon;

use App\Enums\RruleFreq;
use App\Enums\RruleDayOfWeek;
use App\Enums\RruleByMonth;

use RRule\RRule;

class InputsRepeat extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'target',
        'target_id',
        'parent_id',
        'rrule',
    ];

    // RRule配列
    private $rrule_array = null;

    /**
     * RRuleクラス set
     */
    private function setRruleArray() : void
    {
        if (is_null($this->rrule_array)) {
            if (is_null($this->rrule)) {
                // rruleなしは 空配列
                $this->rrule_array = [];
            } else {
                // RRuleクラスは第一引数必須
                $rrule = new RRule($this->rrule);
                $this->rrule_array = $rrule->getRule();
            }
        }
        // [debug]
        // \Log::debug(var_export($this->rrule_array, true));
    }

    /**
     * 繰り返しパターン get
     */
    public function getRruleFreq() : ?string
    {
        $this->setRruleArray();

        return $this->rrule_array['FREQ'] ?? null;
    }

    /**
     * 繰り返し間隔 get
     */
    public function getRruleInterval(?string $select_freq) : ?int
    {
        $this->setRruleArray();

        // 指定した繰り返しパターンなら、INTERVALを返す
        if ($this->getRruleFreq() == $select_freq) {
            return $this->rrule_array['INTERVAL'] ?? null;
        }
        return null;
    }

    /**
     * 繰り返し曜日（週） get
     */
    public function getRruleBydayWeekly(string $day_of_week, ConnectCarbon $default_target_date) : ?string
    {
        $this->setRruleArray();

        // 指定した繰り返しパターンで該当する曜日があったら、曜日の文字を返す
        if ($this->getRruleFreq() == RruleFreq::WEEKLY) {
            $bydays = explode(',', $this->rrule_array['BYDAY']);
            if (in_array($day_of_week, $bydays)) {
                return $day_of_week;
            }
            return null;
        }

        // defaultは 予約日の曜日をチェックON
        return RruleDayOfWeek::getDayOfWeekToKey($default_target_date->dayOfWeek);
    }

    /**
     * 繰り返し間隔-月 get
     */
    public function getRruleRepeatMonthly() : ?string
    {
        $this->setRruleArray();

        if ($this->getRruleFreq() == RruleFreq::MONTHLY) {
            $bymonthday = $this->rrule_array['BYMONTHDAY'] ?? null;
            $byday = $this->rrule_array['BYDAY'] ?? null;

            if (is_null($bymonthday) && is_null($byday)) {
                // default
                return 'BYMONTHDAY';
            } elseif ($byday) {
                // 曜日指定 BYDAY に値あり
                return 'BYDAY';
            } else {
                // 日付指定 BYMONTHDAY とみなす
                return 'BYMONTHDAY';
            }
        }

        // default
        return 'BYMONTHDAY';
    }

    /**
     * 繰り返し間隔-月-日付指定 get
     */
    public function getRruleBymonthday() : ?string
    {
        $this->setRruleArray();

        if ($this->getRruleRepeatMonthly() == 'BYMONTHDAY') {
            return $this->rrule_array['BYMONTHDAY'] ?? null;
        }

        // default
        return null;
    }

    /**
     * 繰り返し間隔-月-曜日指定 get
     */
    public function getRruleBydayMonthly() : ?string
    {
        $this->setRruleArray();

        if ($this->getRruleRepeatMonthly() == 'BYDAY') {
            return $this->rrule_array['BYDAY'] ?? null;
        }

        // default
        return null;
    }

    /**
     * 繰り返し月（年） get
     */
    public function getRruleBymonthsYearly(int $month, ConnectCarbon $default_target_date) : ?string
    {
        $this->setRruleArray();

        // 指定した繰り返しパターンで該当する月があったら、月の数字を返す
        if ($this->getRruleFreq() == RruleFreq::YEARLY) {
            $bymonths = explode(',', $this->rrule_array['BYMONTH']);
            if (in_array($month, $bymonths)) {
                return $month;
            }
            return null;
        }

        // defaultは 予約日の月をチェックON
        return $default_target_date->month;
    }

    /**
     * 繰り返し間隔-年-曜日指定 get
     */
    public function getRruleBydayYearly() : ?string
    {
        $this->setRruleArray();

        if ($this->getRruleFreq() == RruleFreq::YEARLY) {
            return $this->rrule_array['BYDAY'] ?? null;
        }

        // default
        return null;
    }

    /**
     * 繰り返し終了 get
     */
    public function getRruleRepeatEnd() : ?string
    {
        $this->setRruleArray();

        $until = $this->rrule_array['UNTIL'] ?? null;
        $count = $this->rrule_array['COUNT'] ?? null;

        if (is_null($until) && is_null($count)) {
            // default
            return 'COUNT';
        } elseif ($until) {
            // 指定日 UNTIL に値あり
            return 'UNTIL';
        } else {
            // 指定の回数後 COUNT とみなす
            return 'COUNT';
        }
    }

    /**
     * 繰り返し終了-指定の回数後 get
     */
    public function getRruleCount() : ?string
    {
        $this->setRruleArray();

        if ($this->getRruleRepeatEnd() == 'COUNT') {
            return $this->rrule_array['COUNT'] ?? null;
        }
        return null;
    }

    /**
     * 繰り返し終了-指定日 get
     */
    public function getRruleUntil($is_display = false) : ?string
    {
        $this->setRruleArray();

        if ($this->getRruleRepeatEnd() == 'UNTIL') {
            if (isset($this->rrule_array['UNTIL'])) {
                // 値あり（DateTime）

                if ($is_display) {
                    // 詳細画面
                    return $this->rrule_array['UNTIL']->format(__('messages.format_date'));
                } else {
                    // 編集画面
                    return $this->rrule_array['UNTIL']->format('Y-m-d');
                }

            } else {
                return null;
            }
        }
        return null;
    }

    /**
     * 繰り返し-内容表示
     */
    public function showRruleDisplay() : ?string
    {
        $this->setRruleArray();

        $rrule_display = '';

        // 繰り返し間隔
        $interval = $this->getRruleInterval($this->getRruleFreq());
        if (is_null($interval)) {
            return null;
        }

        if ($this->getRruleFreq() == RruleFreq::DAILY) {
            // 毎日

            // 多言語 複数形指定
            return trans_choice('messages.rrule_daily', $interval, ['interval' => $interval]);

        } elseif ($this->getRruleFreq() == RruleFreq::WEEKLY) {
            // 毎週

            // 毎週, 月,火      Weekly, Sun,Mon
            // 2週間ごと, 月,火  Every 2 weeks, Sun,Mon
            $rrule_display = trans_choice('messages.rrule_weekly', $interval, ['interval' => $interval]);
            $rrule_display .= ', ';

            // 指定した繰り返しパターンで該当する曜日があったら、曜日の文字を返す
            $bydays = explode(',', $this->rrule_array['BYDAY']);
            foreach ($bydays as $byday) {
                $rrule_display .= RruleDayOfWeek::getDescription($byday) . ',';
            }

            // 末尾カンマ削除
            return rtrim($rrule_display, ',');

        } elseif ($this->getRruleFreq() == RruleFreq::MONTHLY) {
            // 毎月

            // 表示例）
            // 毎月, 1日           Monthly, 1 day
            // 毎月, 6日           Monthly, 6 days
            // 毎月, 第1日曜日      Monthly, 1st Sunday
            // 2ヶ月ごと, 6日       Every 2 months, 6 days
            // 2ヶ月ごと, 第1日曜日 Every 2 months, 1st Sunday
            // 2ヶ月ごと, 最終日曜日 Every 2 months, Last Sunday
            $rrule_display = trans_choice('messages.rrule_monthly', $interval, ['interval' => $interval]);
            $rrule_display .= ', ';

            if ($this->getRruleRepeatMonthly() == 'BYDAY') {
                // 曜日指定 BYDAY

                $byday = $this->getRruleBydayMonthly();

                $rrule_display .= RruleDayOfWeek::getDescriptionBydayMonthly($byday);
                return $rrule_display;

            } else {
                // 日付指定 BYMONTHDAY とみなす

                $rrule_display .= trans_choice('messages.rrule_bymonthday', $this->rrule_array['BYMONTHDAY'], ['day' => $this->rrule_array['BYMONTHDAY']]);
                return $rrule_display;
            }

        } elseif ($this->getRruleFreq() == RruleFreq::YEARLY) {
            // 毎年

            // 表示例）
            // 毎年, 1月, 2月, 開始日と同日    Yearly, January, February, same day as the start date
            // 毎年, 1月, 2月, 第1日曜日       Yearly, January, February, 1st Sunday
            // 2年ごと, 1月, 2月, 第1日曜日    Every 2 years, January, February, 1st Sunday
            // 2年ごと, 1月, 2月, 最終日曜日   Every 2 years, January, February, Last Sunday
            $rrule_display = trans_choice('messages.rrule_yearly', $interval, ['interval' => $interval]);
            $rrule_display .= ', ';

            // 月の文字を返す
            $bymonths = explode(',', $this->rrule_array['BYMONTH']);
            foreach ($bymonths as $bymonth) {
                $rrule_display .= RruleByMonth::getDescription($bymonth) . ', ';
            }

            // 曜日指定（年）
            $byday = $this->getRruleBydayYearly();
            if (is_null($byday)) {
                // 開始日と同日
                $rrule_display .= __('messages.rrule_yearly_same_day');
            } else {
                $rrule_display .= RruleDayOfWeek::getDescriptionBydayMonthly($byday);
            }

            return $rrule_display;
        }

        return null;
    }

    /**
     * 繰り返し終了-内容表示
     */
    public function showRruleEndDisplay() : ?string
    {
        $this->setRruleArray();

        if ($this->getRruleRepeatEnd() == 'UNTIL') {
            // 指定日 UNTIL に値あり

            // true = 詳細画面用の指定日表示
            $until = $this->getRruleUntil(true);
            if (is_null($until)) {
                return null;
            }

            return __('messages.rrule_until', ['until' => $until]);

        } else {
            // 指定の回数後 COUNT とみなす

            $count = $this->getRruleCount();
            if (is_null($count)) {
                return null;
            }

            return __('messages.rrule_count', ['count' => $count]);
        }
    }

}
