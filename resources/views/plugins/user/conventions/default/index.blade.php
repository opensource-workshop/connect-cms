{{--
 * イベント画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category イベント管理プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if (isset($frame) && $frame->bucket_id)
    {{-- バケツあり --}}
@else
    @can('frames.edit',[[null, null, null, $frame]])
    {{-- バケツなし --}}
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するイベントを選択するか、作成してください。</p>
        </div>
    </div>
    @endcan
@endif

@if (isset($posts))
{{-- トラックの表 --}}
<table class="table table-bordered">
<thead>
    <tr>
    <td></td>
    @for ($j = 1; $j <= $convention->track_count; $j++)
        <td>トラック-{{$j}}</td>
    @endfor
    </tr>
</thead>
<tbody>
@for ($i = 1; $i <= $convention->period_count; $i++)
    <tr>
        <td rowspan="2">{{$tool->getPeriodLabel($i)}}</td>
        @for ($j = 1; $j <= $convention->track_count; $j++)
        <td class="border-bottom-0">
            <p>
                @can('posts.create',[[null, 'conventions', $buckets]])
                    @if ($posts->where('track', $j)->where('period', $i)->first())
                    <a href="{{url('/')}}/plugin/conventions/edit/{{$page->id}}/{{$frame_id}}/{{$posts->where('track', $j)->where('period', $i)->first()->id}}?track={{$j}}&period={{$i}}#frame-{{$frame_id}}"><i class="far fa-edit"></i></a>
                    @else
                    <a href="{{url('/')}}/plugin/conventions/edit/{{$page->id}}/{{$frame_id}}?track={{$j}}&period={{$i}}#frame-{{$frame_id}}"><i class="far fa-edit"></i></a>
                    @endif
                @endcan
                {{$tool->getTitle($j, $i)}}
            </p>
            {!!nl2br($tool->getDescription($j, $i))!!}
            {!!nl2br($tool->getLinkTag($j, $i))!!}
        </td>
        @endfor
    </tr>
    <tr>
        @for ($j = 1; $j <= $convention->track_count; $j++)
        <td class="border-top-0">
            @if ($tool->hasPeriod($j, $i))
            @auth
                @if ($tool->isJoin($j, $i))
                    <form action="{{url('/')}}/redirect/plugin/conventions/joinOff/{{$page->id}}/{{$frame_id}}/{{$tool->getPostId($j, $i)}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <input type="hidden" name="join_flag" value="1">
                        <div class="text-center">
                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> 参加を取り消す</button>
                        </div>
                    </form>
                @else
                    <form action="{{url('/')}}/redirect/plugin/conventions/join/{{$page->id}}/{{$frame_id}}/{{$tool->getPostId($j, $i)}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <input type="hidden" name="join_flag" value="1">
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 参加する</button>
                        </div>
                    </form>
                @endif
            @endauth
            @endif
        </td>
        @endfor
    </tr>
@endfor
</tbody>
</table>
@endif

@endsection
