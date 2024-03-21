{{--
 * エラーメッセージのテンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@can('frames.edit',[[null, null, null, $frame]])
<div class="card border-danger">
    <div class="card-body">
        @foreach ($error_messages as $error_message)
            <p class="text-center cc_margin_bottom_0">{!! nl2br(e($error_message)) !!}</p>
        @endforeach
    </div>
</div>
@endcan
@endsection
