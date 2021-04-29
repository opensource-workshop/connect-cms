{{--
 * カウンター画面デザインテンプレート・丸数字。
--}}
@php
    $count = $count ?? 0;
    $count_chars = str_split($count);

    $before = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $after = ['⓪', '①', '②', '③', '④', '⑤', '⑥', '⑦', '⑧', '⑨'];
@endphp

{{$count_title}}
@foreach ($count_chars as $count_char){{str_replace($before, $after, $count_char)}}@endforeach
