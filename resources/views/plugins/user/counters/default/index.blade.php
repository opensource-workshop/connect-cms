{{--
 * カウンター画面テンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if ($plugin_frame->use_total_count)
    <div>
        @include('plugins.user.counters.default.index_design_' . $plugin_frame->design_type, [
            'count_title' => $plugin_frame->total_count_title,
            'count' => $counter_count->total_count
        ])
    </div>
@endif

@if ($plugin_frame->use_today_count)
    <div>
        @include('plugins.user.counters.default.index_design_' . $plugin_frame->design_type, [
            'count_title' => $plugin_frame->today_count_title,
            'count' => $counter_count->day_count
        ])
    </div>
@endif

@if ($plugin_frame->use_yestday_count)
    <div>
        @include('plugins.user.counters.default.index_design_' . $plugin_frame->design_type, [
            'count_title' => $plugin_frame->yestday_count_title,
            'count' => $counter_count->yesterday_count
        ])
    </div>
@endif

@endsection
