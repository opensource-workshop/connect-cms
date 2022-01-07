{{--
 * 施設予約 予約の詳細表示画面
 --}}
 @extends('core.cms_frame_base')

 @section("plugin_contents_$frame->id")

@php
if ($frame->isExpandNarrow()) {
    // 右・左エリア = スマホ表示と同等にする
    $label_class = 'col-12';
    $value_class = 'col-12';
} else {
    // メインエリア・フッターエリア
    $label_class = 'col-sm-2 py-1';
    $value_class = 'col-sm-10 py-1';
}
@endphp

<dl class="row">
    {{-- 施設名 --}}
    <dt class="{{$label_class}}">{{__('messages.facility_name')}}</dt>
    <dd class="{{$value_class}}">{{$inputs->facility_name}}</dd>
    {{-- 利用日 --}}
    <dt class="{{$label_class}}">{{__('messages.day_of_use')}}</dt>
    <dd class="{{$value_class}}">{{$inputs->displayDate()}}</dd>
    {{-- 利用時間 --}}
    <dt class="{{$label_class}}">{{__('messages.time_of_use')}}</dt>
    <dd class="{{$value_class}}">{{$inputs->start_datetime->format('H:i')}} ~ {{$inputs->end_datetime->format('H:i')}}</dd>

    @if ($repeat->id)
        {{-- 繰り返し --}}
        <dt class="{{$label_class}}">{{__('messages.repetition')}}</dt>
        <dd class="{{$value_class}}">
            {{$repeat->showRruleDisplay()}}<br />
            {{$repeat->showRruleEndDisplay()}}
        </dd>
    @endif

    @foreach($columns as $column)
        <dt class="{{$label_class}}">{{$column->column_name}}</dt>
        <dd class="{{$value_class}}">
            @include('plugins.user.reservations.default.include_show_value')
        </dd>
    @endforeach
</dl>

@can('role_update_or_approval', [[$inputs, $frame->plugin_name, $buckets]])
<div class="row mt-2">
    <div class="col-12 text-right mb-1">
        @if ($inputs->status == StatusType::approval_pending)
            @can('role_update_or_approval', [[$inputs, $frame->plugin_name, $buckets]])
                <span class="badge badge-warning align-bottom">承認待ち</span>
            @endcan
            @can('posts.approval', [[$inputs, $frame->plugin_name, $buckets]])
                <form action="{{url('/')}}/redirect/plugin/reservations/approvalBooking/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}#frame-{{$frame_id}}" method="post" name="form_approval" class="d-inline">
                    {{ csrf_field() }}
                    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/reservations/showBooking/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}#frame-{{$frame_id}}">
                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                        <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                    </button>
                </form>
            @endcan
        @endif

        @can('posts.update', [[$inputs, $frame->plugin_name, $buckets]])
            <a class="btn btn-success btn-sm" href="{{url('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}#frame-{{$frame_id}}">
                <i class="far fa-edit"></i> 編集
            </a>
        @endcan
    </div>
</div>
@endcan

{{-- 一覧へ戻る --}}
<nav class="row" aria-label="ページ移動">
    <div class="col-12 text-center my-3">
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</nav>

 @endsection
