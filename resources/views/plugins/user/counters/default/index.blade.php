{{--
 * カウンター画面テンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@php
// 表示設定がない時（※）は、数字（カンマなし）で表示。
// ※ 表示設定はある想定のため、基本ありえない。
$design_type = $plugin_frame->design_type ?? CounterDesignType::numeric;
@endphp

@if ($plugin_frame->use_total_count)
    <div>
        @include('plugins.user.counters.default.index_design_' . $design_type, [
            'count_title' => $plugin_frame->total_count_title,
            'count' => $counter_count->total_count,
            'count_after' => $plugin_frame->total_count_after,
        ])
    </div>
@endif

@if ($plugin_frame->use_today_count)
    <div>
        @include('plugins.user.counters.default.index_design_' . $design_type, [
            'count_title' => $plugin_frame->today_count_title,
            'count' => $counter_count->day_count,
            'count_after' => $plugin_frame->today_count_after,
        ])
    </div>
@endif

@if ($plugin_frame->use_yestday_count)
    <div>
        @include('plugins.user.counters.default.index_design_' . $design_type, [
            'count_title' => $plugin_frame->yestday_count_title,
            'count' => $counter_count->yesterday_count,
            'count_after' => $plugin_frame->yestday_count_after,
        ])
    </div>
@endif

@endsection
