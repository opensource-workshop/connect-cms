{{--
 * 日の表示

 * @param $date ConnectCarbon 日付データ
--}}
@if ($date == Carbon::today())
    {{-- 今日 --}}
    @if ($date->dayOfWeek == DayOfWeek::sun || ($date->hasHoliday()))
        <span class="fa-stack small cc-color-sunday">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-inverse fa-stack-1x">{{$date->day}}</i>
        </span>
    @elseif ($date->dayOfWeek == DayOfWeek::sat)
        <span class="fa-stack small cc-color-saturday">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-inverse fa-stack-1x">{{$date->day}}</i>
        </span>
    @else
        <span class="fa-stack small">
            <i class="fa fa-circle fa-stack-2x"></i>
            <i class="fa fa-inverse fa-stack-1x">{{$date->day}}</i>
        </span>
    @endif
@else
    {{-- 今日以外 --}}
    @if ($date->dayOfWeek == DayOfWeek::sun || ($date->hasHoliday()))
        <span class="cc-color-sunday">{{$date->day}}</span>
    @elseif ($date->dayOfWeek == DayOfWeek::sat)
        <span class="cc-color-saturday">{{$date->day}}</span>
    @else
        {{$date->day}}
    @endif
@endif
