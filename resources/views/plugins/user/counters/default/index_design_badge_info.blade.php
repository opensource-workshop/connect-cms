{{--
 * カウンター画面デザインテンプレート・badgeシリーズ。
--}}
@php
    $count = $count ?? 0;
    $count_chars = str_split($count);
@endphp

{{$count_title}}
@foreach ($count_chars as $count_char)<span class="badge badge-info text-monospace" style="margin-left: 1px">{{$count_char}}</span>@endforeach
