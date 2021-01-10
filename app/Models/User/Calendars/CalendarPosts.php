<?php

namespace App\Models\User\Calendars;

use Illuminate\Support\Collection;

/**
 * カレンダー・記事用Collection
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダー・プラグイン
 * @package モデル
 */
class CalendarPosts extends Collection
{
    /**
     * 対象のデータ
     * $date = 'Y-m-d' の形式を想定
     */
    public function wherePostFromDate($date)
    {
        // 開始日 <= 表示日 >= 終了日をフィルタで抽出する。
        return $this->filter(function ($value, $key) use ($date) {
            if ($value->start_date <= $date && $value->end_date >= $date) {
                return true;
            }
            return false;
        });
        return $this;
    }
}
