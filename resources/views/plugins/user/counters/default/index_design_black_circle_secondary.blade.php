{{--
 * カウンター画面デザインテンプレート・黒丸数字シリーズ。
--}}
@php
    $count = $count ?? 0;
    $count_chars = str_split($count);

    $before = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $after = ['⓿', '❶', '❷', '❸', '❹', '❺', '❻', '❼', '❽', '❾'];
@endphp

{{$count_title}}
<span class="text-secondary">
    @foreach ($count_chars as $count_char){{str_replace($before, $after, $count_char)}}@endforeach
</span>
{{$count_after}}
