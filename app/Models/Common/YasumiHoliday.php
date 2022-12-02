<?php

namespace App\Models\Common;

use Yasumi\Holiday;
use Yasumi\Yasumi;

class YasumiHoliday extends Holiday
{
    /**
     * 独自祝日設定ステータス（追加プロパティ）
     * null：初期値（ない想定）、1：追加、2：上書き無効
     */
    public $orginal_holiday_status = null;

    /**
     * 独自祝日データ（追加プロパティ）
     */
    public $orginal_holiday_post = null;

    /**
     * コンストラクタ
     */
    public function __construct(
        string $key,
        array $names,
        \DateTimeInterface $date,
        string $displayLocale = self::DEFAULT_LOCALE,
        $orginal_holiday_status = null
    ) {
        parent::__construct($key, $names, $date, $displayLocale);
        $this->orginal_holiday_status = $orginal_holiday_status;
    }

    /**
     * 年の祝日を取得
     */
    public static function getYasumis($year, ?string $country = 'Japan', ?string $locale = 'ja_JP') : \Yasumi\Provider\AbstractProvider
    {
        return Yasumi::create($country, (int)$year, $locale);
    }
}
