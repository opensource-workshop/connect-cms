{{--
 * カレンダー画面テンプレート。
 *
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<div class="text-center mb-1">
    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year={{date('Y', strtotime('-1 day', $current_ym_first))}}&month={{date('m', strtotime('-1 day', $current_ym_first))}}&day={{date('d', strtotime('-1 day', $current_ym_first))}}#frame-{{$frame_id}}"><i class="fas fa-chevron-circle-left"></i></a>

    {{-- 右・左エリアは 年 を表示しない --}}
    @if ($frame->area_id == LayoutArea::left || $frame->area_id == LayoutArea::right)
        <div class="h5 d-inline">
            {{date('n', $current_ym_first)}}月{{date('j', $current_ym_first)}}日
        </div>
        <div class="d-inline">({{  DayOfWeek::getDescription(date('w', $current_ym_first))  }})</div>
    @else
        <div class="h5 d-inline">{{date('Y', $current_ym_first)}}年</div>
        <div class="h3 d-inline">
            {{date('n', $current_ym_first)}}月{{date('j', $current_ym_first)}}日
        </div>
        <div class="h5 d-inline">({{  DayOfWeek::getDescription(date('w', $current_ym_first))  }})</div>
    @endif

    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year={{date('Y', strtotime('+1 day', $current_ym_first))}}&month={{date('m', strtotime('+1 day', $current_ym_first))}}&day={{date('d', strtotime('+1 day', $current_ym_first))}}#frame-{{$frame_id}}"><i class="fas fa-chevron-circle-right"></i></a>

    {{-- 右・左エリアは「今日へ」表示しない --}}
    @if ($frame->area_id == LayoutArea::left || $frame->area_id == LayoutArea::right)
    @else
        <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year={{date('Y')}}&month={{date('m')}}&day={{date('d')}}#frame-{{$frame_id}}"><div class="badge badge-pill badge-info align-bottom ml-3">今日へ</div></a>
    @endif
</div>

<table class="table table-bordered">
    @php
    $date = $dates[date('Y-m-d', $current_ym_first)];
    @endphp

    <tbody>
        @if ($date->dayOfWeek == 0)
        <tr>
        @endif
            <td class="d-block">
            <div class="row">
                <div class="col-6 font-weight-bold text-secondary text-nowrap">
                    @if ($date->format('Y-m-d') == date('Y-m-d'))
                        {{-- 今日 --}}
                        @if ($date->dayOfWeek == 0 || ($date->hasHoliday()))
                        <span class="fa-stack small cc-color-sunday">
                            <i class="fa fa-circle fa-stack-2x"></i>
                            <i class="fa fa-inverse fa-stack-1x">{{$date->day}}</i>
                        </span>
                        @elseif ($date->dayOfWeek == 6)
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
                        @if ($date->dayOfWeek == 0 || ($date->hasHoliday()))
                        <span class="cc-color-sunday">{{$date->day}}</span>
                        @elseif ($date->dayOfWeek == 6)
                        <span class="cc-color-saturday">{{$date->day}}</span>
                        @else
                        {{$date->day}}
                        @endif
                    @endif

                    {{-- 曜日表示 --}}
                    <div class="d-inline">
                        @if ($date->dayOfWeek == 0 || ($date->hasHoliday()))
                        <span class="cc-color-sunday">({{$date->formatLocalized("%a")}})</span>
                        @elseif ($date->dayOfWeek == 6)
                        <span class="cc-color-saturday">({{$date->formatLocalized("%a")}})</span>
                        @else
                        ({{$date->formatLocalized("%a")}})
                        @endif
                        <div class="col-12 pl-1 d-inline cc-font-90">
                            <span class="badge badge-pill badge-danger">{{$date->getHolidayName()}}</span>
                        </div>
                    </div>

                </div>
                <div class="col-6 text-right">
                @can('posts.create',[[null, 'calendars', $buckets]])
                    @if (isset($frame) && $frame->bucket_id)
                        {{-- 新規登録ボタン --}}
                        <a href="{{url('/')}}/plugin/calendars/edit/{{$page->id}}/{{$frame_id}}?date={{$date->format('Y-m-d')}}#frame-{{$frame_id}}"><i class="fas fa-plus"></i></a>
                    @endif
                @endcan
                </div>
            </div>
            {{-- 祝日 --}}
            @if ($date->hasHoliday())
                <div class="row py-1 d-none d-md-block">
                    <div class="col-12 cc-font-90">
                        <span class="badge badge-pill badge-danger">{{$date->getHolidayName()}}</span>
                    </div>
                </div>
            @endif
            {{-- 拡張Collection を使用して表示するべき予定を抽出する --}}
            @foreach($posts->wherePostFromDate($date->format('Y-m-d')) as $post)
                <div class="row py-1">
                    <div class="d-md-none col-1"></div>
                    <div class="col-11 col-md-12">
                        @if ($post->allday_flag == 0)
                            <div class="cc-font-80">{{$post->getStartTime($date->format('Y-m-d'))}} - {{$post->getEndTime($date->format('Y-m-d'))}}</div>
                        @endif
                        {!!$post->getStatusBadge(true)!!}
                        <div class="cc-font-90"><a href="{{url('/')}}/plugin/calendars/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">{{$post->title}}</a></div>
                    </div>
                </div>
            @endforeach
            </td>
        @if ($date->dayOfWeek == 6)
        </tr>
        @endif

    </tbody>
</table>

@endsection
