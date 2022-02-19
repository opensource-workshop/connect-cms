{{--
 * 曜日の表示
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 *
 * @param $date ConnectCarbon 日付データ
--}}
@if ($date->dayOfWeek == DayOfWeek::sun || ($date->hasHoliday()))
    <span class="cc-color-sunday">{{ '(' . DayOfWeek::getDescription($date->dayOfWeek) . ')' }}</span>
@elseif ($date->dayOfWeek == DayOfWeek::sat)
    <span class="cc-color-saturday">{{ '(' . DayOfWeek::getDescription($date->dayOfWeek) . ')' }}</span>
@else
    {{ '(' . DayOfWeek::getDescription($date->dayOfWeek) . ')' }}
@endif
