{{--
 * 日付入力テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@php
    // TempusDominusの設定
    $element_id = $element_id ?? null;
    $side_by_side = $side_by_side ?? false;
    $format = $format ?? 'yyyy-MM-dd HH:mm';
    $seconds = $seconds ?? false;
    $clock_icon = $clock_icon ?? true;
    $calendar_icon = $calendar_icon ?? true;
    $stepping = $stepping ?? 1;
    $view_mode = $view_mode ?? 'calendar';

    // 設定のみオプション
    $is_setting_only = $is_setting_only ?? false;
@endphp

<script type="text/javascript">
    const picker_setting_{{$element_id}} = {
        localization: {
            locale: '{{ App::getLocale() }}',
            dayViewHeaderFormat: { year: 'numeric', month: 'long' },
            format: '{{$format}}',
            @if (App::getLocale() == ConnectLocale::ja)
                today: '本日',
                close: '閉じる',
                selectMonth: '月を選択',
                previousMonth: '前月',
                nextMonth: '次月',
                selectYear: '年を選択',
                previousYear: '前年',
                nextYear: '次年',
                selectDecade: '期間を選択',
                previousDecade: '前期間',
                nextDecade: '次期間',
                previousCentury: '前世紀',
                nextCentury: '次世紀',
                pickHour: '時間を取得',
                incrementHour: '時間を増加',
                decrementHour: '時間を減少',
                pickMinute: '分を取得',
                incrementMinute: '分を増加',
                decrementMinute: '分を減少',
                pickSecond: '秒を取得',
                incrementSecond: '秒を増加',
                decrementSecond: '秒を減少',
                toggleMeridiem: '午前/午後切替',
                selectTime: '時間を選択',
            @endif
        },
        display: {
            viewMode: '{{$view_mode}}',
            components: {
                @if ($seconds)
                    seconds: true,
                @endif
                @if (!$clock_icon)
                    clock: false,
                @endif
                @if (!$calendar_icon)
                    calendar: false,
                @endif
            },
            @if ($side_by_side)
                sideBySide: true,
            @endif
            theme: 'light',
        },
        stepping: {{$stepping}},
    }

    @if (!$is_setting_only)
        const picker_{{$element_id}} = new tempusDominus.TempusDominus(document.getElementById('{{$element_id}}'), picker_setting_{{$element_id}});
    @endif
</script>
