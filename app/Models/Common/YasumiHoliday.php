<?php

namespace App\Models\Common;

use Illuminate\Support\Collection;

use Yasumi\Holiday;
use Yasumi\Yasumi;

class YasumiHoliday extends Holiday
{
    /**
     * 独自祝日設定ステータス（追加プロパティ）
     * null：初期値（ない想定）、1：追加、2：上書き無効
     */
    public $original_holiday_status = null;

    /**
     * 独自祝日データ（追加プロパティ）
     */
    public $original_holiday_post = null;

    /**
     * コンストラクタ
     */
    public function __construct(
        string $key,
        array $names,
        \DateTimeInterface $date,
        string $displayLocale = self::DEFAULT_LOCALE,
        $original_holiday_status = null
    ) {
        parent::__construct($key, $names, $date, $displayLocale);
        $this->original_holiday_status = $original_holiday_status;
    }

    /**
     * 年の祝日を取得
     */
    public static function getYasumis($year, ?string $country = 'Japan', ?string $locale = 'ja_JP') : \Yasumi\Provider\AbstractProvider
    {
        return Yasumi::create($country, (int)$year, $locale);
    }

    /**
     * 独自設定祝日を加味する。
     */
    public static function addConnectHolidays(Collection $connect_holidays, \Yasumi\Provider\AbstractProvider $yasumis) : \Yasumi\Provider\AbstractProvider
    {
        foreach ($connect_holidays as $holiday) {
            // 計算の祝日に同じ日があれば、追加設定を有効にするために、かぶせる。
            // Yasumi のメソッドに日付指定での抜き出しがないので、ループする。
            $found_flag = false;
            foreach ($yasumis as &$yasumi) {
                if ($yasumi->format('Y-m-d') == $holiday->holiday_date) {
                    // 独自設定の祝日と同じ日が計算の祝日にあれば、計算の祝日を消して、独自設定を有効にする。
                    $found_flag = true;
                    $yasumis->removeHoliday($yasumi->shortName);
                    $new_holiday = new YasumiHoliday($holiday->id, ['ja_JP' => $holiday->holiday_name], new ConnectCarbon($holiday->holiday_date), 'ja_JP', 1);
                    $new_holiday->original_holiday_post = $holiday;
                    $yasumis->addHoliday($new_holiday);
                    break;
                }
            }
            // 計算の祝日にない独自設定は、追加祝日として扱う。
            if ($found_flag == false) {
                $new_holiday = new YasumiHoliday($holiday->id, ['ja_JP' => $holiday->holiday_name], new ConnectCarbon($holiday->holiday_date), 'ja_JP', 1);
                $new_holiday->original_holiday_post = $holiday;
                $yasumis->addHoliday($new_holiday);
            }
        }

        return $yasumis;
    }
}
