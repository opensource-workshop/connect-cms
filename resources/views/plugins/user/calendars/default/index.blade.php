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
    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year={{date('Y', strtotime('-1 month', $current_ym_first))}}&month={{date('m', strtotime('-1 month', $current_ym_first))}}#frame-{{$frame_id}}"><i class="fas fa-chevron-circle-left"></i></a>
    <h5 class="d-inline">{{date('Y', $current_ym_first)}}年</h5>
    <h3 class="d-inline">{{date('n', $current_ym_first)}}月</h3>
    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year={{date('Y', strtotime('+1 month', $current_ym_first))}}&month={{date('m', strtotime('+1 month', $current_ym_first))}}#frame-{{$frame_id}}"><i class="fas fa-chevron-circle-right"></i></a>
    <a href="{{url('/')}}/plugin/calendars/index/{{$page->id}}/{{$frame_id}}?year={{date('Y')}}&month={{date('m')}}#frame-{{$frame_id}}"><div class="badge badge-pill badge-info align-bottom ml-3">今月へ</div></a>
</div>

<table class="table table-bordered">
    <thead>
    <tr class="thead d-none d-md-table-row">
        @foreach (['日', '月', '火', '水', '木', '金', '土'] as $dayOfWeek)
        <th class="cc-w13pct text-center">{{ $dayOfWeek }}</th>
        @endforeach
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
                    <div class="col-6 font-weight-bold text-secondary">
                        {{$date->day}}
                        <div class="d-md-none d-inline">({{$date->formatLocalized("%a")}})</div>
                    </div>
                    <div class="col-6 text-right">
                    @if ($date->month == $current_month)
                        @can('posts.create',[[null, 'calendars', $buckets]])
                            @if (isset($frame) && $frame->bucket_id)
                                {{-- 新規登録ボタン --}}
                                <a href="{{url('/')}}/plugin/calendars/edit/{{$page->id}}/{{$frame_id}}?date={{$date->format('Y-m-d')}}#frame-{{$frame_id}}"><i class="fas fa-plus"></i></a>
                            @endif
                        @endcan
                    @endif
                    </div>
                </div>
                {{-- 拡張Collection を使用して表示するべき予定を抽出する --}}
                @foreach($posts->wherePostFromDate($date->format('Y-m-d')) as $post)
                    <div class="row py-1">
                        <div class="d-md-none col-1"></div>
                        <div class="col-11 col-md-12">
                            <div class="cc-font-80">{{$post->getStartTime()}} - {{$post->getEndTime()}}</div>
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
