{{--
 * カレンダー画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<div class="text-center mb-1">
    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year{{$frame_id}}={{date('Y', strtotime('-1 month', $current_ym_first))}}&month{{$frame_id}}={{date('m', strtotime('-1 month', $current_ym_first))}}#frame-{{$frame_id}}"><i class="fas fa-chevron-circle-left"></i></a>
    <h5 class="d-inline">{{date('Y', $current_ym_first)}}年</h5>
    <h3 class="d-inline">{{date('n', $current_ym_first)}}月</h3>
    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year{{$frame_id}}={{date('Y', strtotime('+1 month', $current_ym_first))}}&month{{$frame_id}}={{date('m', strtotime('+1 month', $current_ym_first))}}#frame-{{$frame_id}}"><i class="fas fa-chevron-circle-right"></i></a>

    <div class="d-inline align-bottom ml-3">
        <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year{{$frame_id}}={{date('Y')}}&month{{$frame_id}}={{date('m')}}#frame-{{$frame_id}}" class="badge badge-pill badge-info">
            今月へ
        </a>
    </div>
</div>

{{-- posts.createをループ外で判定 --}}
@can('posts.create',[[null, $frame->plugin_name, $buckets]])
    @php $can_posts_create = true; @endphp
@else
    @php $can_posts_create = false; @endphp
@endcan

<table class="table table-bordered">
    <thead>
    <tr class="thead d-none d-md-table-row">
        <th class="cc-w13pct text-center cc-color-sunday">日</th>
        @foreach (['月', '火', '水', '木', '金'] as $dayOfWeek)
        <th class="cc-w13pct text-center">{{ $dayOfWeek }}</th>
        @endforeach
        <th class="cc-w13pct text-center cc-color-saturday">土</th>
    </tr>
    </thead>
    <tbody>
        @foreach ($dates as $date)
            @if ($date->dayOfWeek == 0)
            <tr>
            @endif
                <td
                    @if ($date->month != $current_month)
                        class="d-none d-md-table-cell bg-light"
                    @else
                        class="d-block d-md-table-cell"
                    @endif
                >
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
                        <div class="d-md-none d-inline">
                            @if ($date->dayOfWeek == 0 || ($date->hasHoliday()))
                            <span class="cc-color-sunday">({{$date->formatLocalized("%a")}})</span>
                            @elseif ($date->dayOfWeek == 6)
                            <span class="cc-color-saturday">({{$date->formatLocalized("%a")}})</span>
                            @else
                            ({{$date->formatLocalized("%a")}})
                            @endif
                            {{-- 祝日 --}}
                            @if ($date->hasHoliday())
                                <div class="pl-1 d-inline cc-font-90">
                                    <span class="badge badge-pill badge-danger">{{$date->getHolidayName()}}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-6 text-right">
                    @if ($can_posts_create)
                        @if (isset($frame) && $frame->bucket_id)
                            {{-- 新規登録ボタン --}}
                            <a href="{{url('/')}}/plugin/calendars/edit/{{$page->id}}/{{$frame_id}}?date={{$date->format('Y-m-d')}}#frame-{{$frame_id}}"><i class="fas fa-plus"></i></a>
                        @endif
                    @endif
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
        @endforeach
    </tbody>
</table>

@endsection
