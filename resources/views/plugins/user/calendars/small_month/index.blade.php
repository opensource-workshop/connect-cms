{{--
 * カレンダー画面テンプレート。
 *
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
</div>

{{-- posts.createをループ外で判定 --}}
@can('posts.create',[[null, $frame->plugin_name, $buckets]])
    @php $can_posts_create = true; @endphp
@else
    @php $can_posts_create = false; @endphp
@endcan

<table class="table table-bordered table-sm cc-font-80 mb-1">
    <thead>
    <tr class="thead d-table-row">
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
            <tr style="height: 50px;">
            @endif
                <td
                    @if ($date->month != $current_month)
                        class="bg-light text-center"
                    @else
                        class="text-center"
                    @endif
                >
                    <a data-toggle="collapse" href="#collapse-{{$frame_id}}-{{$date->format('Y-m-d')}}" role="button" aria-expanded="false" aria-controls="collapse-{{$frame_id}}-{{$date->format('Y-m-d')}}">

                        <div class="row">
                            <div class="col font-weight-bold text-secondary text-nowrap">
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
                            </div>
                        </div>

                        {{-- 拡張Collection を使用して表示するべき予定を抽出する --}}
                        @foreach($posts->wherePostFromDate($date->format('Y-m-d')) as $post)
                            <div class="cc-font-90">■</div>

                            {{-- 1予定だけで抜ける --}}
                            @break
                        @endforeach

                    </a>
                </td>
            @if ($date->dayOfWeek == 6)
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- 今月へ --}}
<div class="text-right mb-2">
    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year{{$frame_id}}={{date('Y')}}&month{{$frame_id}}={{date('m')}}#frame-{{$frame_id}}" class="badge badge-pill badge-info"><div class="">今月へ</div></a>
</div>

@foreach ($dates as $date)

    {{-- collapse=隠す --}}
    <div
        @if ($date->month != $current_month)
            class="card card-body cc-font-80 p-2 collapse bg-light" id="collapse-{{$frame_id}}-{{$date->format('Y-m-d')}}"

        {{--
        @elseif ($date->format('Y-m-d') == date('Y-m-d'))
            {{-- 今日予定のみ初期表示する
            class="card card-body cc-font-80 p-2 collapse show" id="collapse-{{$frame_id}}-{{$date->format('Y-m-d')}}"
        --}}
        @else
            class="card card-body cc-font-80 p-2 collapse" id="collapse-{{$frame_id}}-{{$date->format('Y-m-d')}}"
        @endif
    >

        <div class="row">
            <div class="col-6 font-weight-bold text-secondary text-nowrap">

                {{-- 日付 --}}
                @if ($date->dayOfWeek == 0 || ($date->hasHoliday()))
                    <span class="cc-color-sunday">{{$date->month}} / {{$date->day}}</span>
                @elseif ($date->dayOfWeek == 6)
                    <span class="cc-color-saturday">{{$date->month}} / {{$date->day}}</span>
                @else
                    {{$date->month}} / {{$date->day}}
                @endif

                {{-- 曜日 --}}
                <div class="d-inline">
                    @if ($date->dayOfWeek == 0 || ($date->hasHoliday()))
                        <span class="cc-color-sunday">({{$date->formatLocalized("%a")}})</span>
                    @elseif ($date->dayOfWeek == 6)
                        <span class="cc-color-saturday">({{$date->formatLocalized("%a")}})</span>
                    @else
                        ({{$date->formatLocalized("%a")}})
                    @endif

                    {{-- 祝日 --}}
                    <div class="col-12 pl-1 d-inline cc-font-90">
                        <span class="badge badge-pill badge-danger">{{$date->getHolidayName()}}</span>
                    </div>
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

        {{-- 拡張Collection を使用して表示するべき予定を抽出する --}}
        @foreach($posts->wherePostFromDate($date->format('Y-m-d')) as $post)
            <div class="row py-1">
                <div class="col">
                    @if ($post->allday_flag == 0)
                        <div class="cc-font-80">{{$post->getStartTime($date->format('Y-m-d'))}} - {{$post->getEndTime($date->format('Y-m-d'))}}</div>
                    @endif
                    {!!$post->getStatusBadge(true)!!}
                    <div class="cc-font-90"><a href="{{url('/')}}/plugin/calendars/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">{{$post->title}}</a></div>
                </div>
            </div>
        @endforeach

        <div class="text-right">
            <a href="#" class="badge badge-pill badge-light" onclick="$('#collapse-{{$frame_id}}-{{$date->format('Y-m-d')}}').collapse('hide'); return false;">閉じる</a>
        </div>
    </div>
@endforeach

@endsection
