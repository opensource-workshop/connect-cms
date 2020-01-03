{{--
 * 開館カレンダー画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contsnts_$frame->id")
<div class="openingcalendar-pdf">
<p>
@can("role_article")
    <a href="{{url('/')}}/plugin/openingcalendars/editYearschedule/{{$page->id}}/{{$frame_id}}/{{$openingcalendar_frame->openingcalendars_id}}#frame-{{$frame->id}}">
        <i class="far fa-edit"></i>
    </a>
@endcan
@if ($openingcalendar_frame->yearschedule_uploads_id)
    <a href="{{url('/')}}/file/{{$openingcalendar_frame->yearschedule_uploads_id}}" target="_blank" rel="noopener">年間カレンダー</a>
@endif
</p>
</div>

<p class="openingcalendar-title">{{$openingcalendar_frame->openingcalendar_name}} / <span>{{$openingcalendar_frame->openingcalendar_sub_name}}</span></p>

<script type="text/javascript">

    {{-- 表示しているアイテムの初期インデックス(後でjQueryでactive 探して設定する) --}}
    ev_current{{$frame_id}} = 0;

    {{-- 初期表示index の設定とイベントのフック --}}
    $(document).ready(function(){
        var i = 0;
        jQuery('#calendar{{$frame_id}} .carousel-inner .carousel-item').each(function(i){
            if (jQuery(this).hasClass('active')) {
                ev_current{{$frame_id}} = i;
            }
            i = i++;
        });

        $("#calendar{{$frame_id}}").on('slide.bs.carousel', function onSlide (ev) {
            // 次のインデックスの設定
            ev_current{{$frame_id}} = ev.to;
        })
    });

    // 戻る
    function prev{{$frame_id}}() {
        // 一番左にいるので、もう動きはない。
        if (ev_current{{$frame_id}} == 0) {
            return;
        }
        //if ($("#calendar{{$frame_id}} .active").attr('data-prev') == "off") {
        //    $("#calendar_prev_link{{$frame_id}}").addClass("disabled");
        //}
        //else {
        //    $("#calendar_next_link{{$frame_id}}").removeClass("disabled");
        //    $("#calendar_prev_link{{$frame_id}}").removeClass("disabled");
        //}
        $("#calendar{{$frame_id}}").carousel('prev');
        $("#view_ym_str{{$frame_id}}").text($("#calendar{{$frame_id}} .active").attr('data-prevmonth'));
    }
    // 進
    function next{{$frame_id}}() {
        // 一番右にいるので、もう動きはない。
        if ($("#calendar{{$frame_id}} .carousel-inner .carousel-item").length <= (ev_current{{$frame_id}} + 1)) {
            return;
        }
        //if ($("#calendar{{$frame_id}} .active").attr('data-next') == "off") {
        //    $("#calendar_next_link{{$frame_id}}").addClass("disabled");
        //}
        //else {
        //    $("#calendar_next_link{{$frame_id}}").removeClass("disabled");
        //    $("#calendar_prev_link{{$frame_id}}").removeClass("disabled");
        //}
        $("#calendar{{$frame_id}}").carousel('next');
        $("#view_ym_str{{$frame_id}}").text($("#calendar{{$frame_id}} .active").attr('data-nextmonth'));
    }


</script>

<div class="openingcalendar-monthWrap">
    <div class="openingcalendar-arrow-left">
        <a href="javascript:prev{{$frame_id}}();" class="@if ($default_disabled['prev'] == 'off') disabled @endif" id="calendar_prev_link{{$frame_id}}">
            <i class="fas fa-chevron-circle-left"></i>
        </a>
    </div>
    <div class="openingcalendar-month">
        <span id="view_ym_str{{$frame_id}}">{{$view_ym_str}}</span>
    </div>
    <div class="openingcalendar-arrow-right">
        <a href="javascript:next{{$frame_id}}();" class="@if ($default_disabled['next'] == 'off') disabled @endif" id="calendar_next_link{{$frame_id}}">
            <i class="fas fa-chevron-circle-right"></i>
        </a>
    </div>
</div>

{{-- <a href="javascript:prev();" class="btn btn-primary @if ($default_disabled['prev'] == 'off') disabled @endif" id="calendar_prev_link{{$frame_id}}"> --}}
{{--
<a href="javascript:prev{{$frame_id}}();" class="@if ($default_disabled['prev'] == 'off') disabled @endif" id="calendar_prev_link{{$frame_id}}">
  <i class="fas fa-chevron-circle-left"></i>
</a>
<span id="view_ym_str{{$frame_id}}">{{$view_ym_str}}</span>
--}}
{{-- <a href="javascript:next();" class="btn btn-primary @if ($default_disabled['next'] == 'off') disabled @endif" id="calendar_next_link{{$frame_id}}"> --}}
{{--
<a href="javascript:next{{$frame_id}}();" class="@if ($default_disabled['next'] == 'off') disabled @endif" id="calendar_next_link{{$frame_id}}">
  <i class="fas fa-chevron-circle-right"></i>
</a>
--}}
{{-- カレンダー --}}
<div id="calendar{{$frame_id}}" class="carousel @if($openingcalendar_frame->smooth_scroll) slide @endif" data-ride="carousel" data-interval=false data-wrap=false>
    <div class="carousel-inner">
        @foreach ($calendars as $calendar_ym => $dates)
        <div class="carousel-item @if($calendar_ym == $view_ym) active @endif" data-prev="{{$view_months[$calendar_ym]["data-prev"]}}" data-next="{{$view_months[$calendar_ym]["data-next"]}}" data-prevmonth="{{$view_months[$calendar_ym]["data-prevmonth"]}}" data-nextmonth="{{$view_months[$calendar_ym]["data-nextmonth"]}}">
            <table class="table table-bordered openingcalendar-month-table">
            <thead>
                <tr>
                @if ($openingcalendar_frame->week_format == 1)
                    @foreach (['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $dayOfWeek)
                    <th class="p-0">{{$dayOfWeek}}</th>
                    @endforeach
                @else
                    @foreach (['日', '月', '火', '水', '木', '金', '土'] as $dayOfWeek)
                    <th class="p-0">{{$dayOfWeek}}</th>
                    @endforeach
                @endif
                </tr>
            </thead>
            <tbody>
            @foreach ($dates as $date)
                @if ($date->dayOfWeek == 0)
                <tr>
                @endif
                    <td
                    @if ($date->format("Y-m") != $calendar_ym)
                        class="bg-secondary p-0"
                    @else
                        @if($view_days[$date->format("Y-m")][$date->format("d")])
                           class="p-0" style="background-color:{{$patterns[$view_days[$date->format("Y-m")][$date->format("d")]]}}"
                        @endif
                    @endif
                    >
                       {{ $date->day }}
                   </td>
                @if ($date->dayOfWeek == 6)
                </tr>
                @endif
            @endforeach
            </tbody>
            </table>

            {{-- パターン --}}
            <div class="openingcalendar-timeLegend">
                @foreach($view_months_patterns[$calendar_ym] as $view_pattern)
                    <dl>
                        <dt><span style="color:{{$view_pattern[0]->color}}">■</span></dt>
                        <dd>{{$view_pattern[0]->pattern}}</dd>
{{--                        <dl>（{{$view_pattern[0]->caption}}）</dl> --}}
                        @if (count($view_pattern) > 1)
                            <dt><span style="color:{{$view_pattern[1]->color}}">■</span></dt>
                            <dd>{{$view_pattern[1]->pattern}}</dd>
{{--                            <dl>（{{$view_pattern[0]->caption}}）</dl> --}}
                        @endif
                    </dl>
                @endforeach
            </div>

            {{-- 月毎のコメント --}}
            @if (isset($view_months[$calendar_ym]['comments']))
            <div class="card mt-2">
                <div class="card-body p-2">{!!$view_months[$calendar_ym]['comments']!!}</div>
            </div>
            @endif

        </div>
        @endforeach
    </div>
</div>

{{-- パターン --}}
{{--
<div class="openingcalendar-timeLegend">
@foreach($patterns_chunks as $patterns_chunk)
    <dl>
        <dt><span style="color:{{$patterns_chunk[0]->color}}">■</span></dt>
        <dd>{{$patterns_chunk[0]->pattern}}</dd>
        <dd>（{{$patterns_chunk[0]->caption}}）</dd>
        @if (count($patterns_chunk) > 1)
            <dt><span style="color:{{$patterns_chunk[1]->color}}">■</span></dt>
            <dd>{{$patterns_chunk[1]->pattern}}</dd>
            <dd>（{{$patterns_chunk[0]->caption}}）</dd>
        @endif
    </dl>
@endforeach
</div>
--}}

@can("role_article")
    <button type="button" class="btn btn-success mt-3" onclick="location.href='{{url('/')}}/plugin/openingcalendars/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="far fa-edit"></i> 編集</button>
@endcan
@endsection
