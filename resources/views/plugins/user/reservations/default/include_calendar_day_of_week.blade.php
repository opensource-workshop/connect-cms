{{--
 * 曜日の表示

 * @param $date ConnectCarbon 日付データ
--}}
@if ($date->dayOfWeek == DayOfWeek::sun || ($date->hasHoliday()))
    <span class="cc-color-sunday">{{ '(' . DayOfWeek::getDescription($date->dayOfWeek) . ')' }}</span>
@elseif ($date->dayOfWeek == DayOfWeek::sat)
    <span class="cc-color-saturday">{{ '(' . DayOfWeek::getDescription($date->dayOfWeek) . ')' }}</span>
@else
    {{ '(' . DayOfWeek::getDescription($date->dayOfWeek) . ')' }}
@endif
