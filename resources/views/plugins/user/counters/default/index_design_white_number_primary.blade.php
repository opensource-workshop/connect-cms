{{--
 * カウンター画面デザインテンプレート・白抜き数字シリーズ。
--}}
@php
    $count = $count ?? 0;
    $count_chars = str_split($count);

    $before = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $after = ['𝟘', '𝟙', '𝟚', '𝟛', '𝟜', '𝟝', '𝟞', '𝟟', '𝟠', '𝟡'];
@endphp

{{$count_title}}
<span class="text-primary">
    @foreach ($count_chars as $count_char){{str_replace($before, $after, $count_char)}}@endforeach
</span>
{{$count_after}}
