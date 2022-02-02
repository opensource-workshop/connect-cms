{{--
 * エラーメッセージのテンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- フォーム利用者向けの業務メッセージ　表示期間外など --}}
@isset($error_messages)
<div class="card border-danger">
    <div class="card-body">
        @foreach ($error_messages as $error_message)
            <p class="text-center cc_margin_bottom_0">{!! nl2br(e($error_message)) !!}</p>
        @endforeach
    </div>
</div>
@endisset

{{-- フォーム設定者向けのシステムメッセージ バケツ未設定など --}}
@isset($setting_error_messages)
@can('frames.edit',[[null, $frame->plugin_name, $buckets]])
<div class="card border-danger">
    <div class="card-body">
        @foreach ($setting_error_messages as $error_message)
            <p class="text-center cc_margin_bottom_0">{!! nl2br(e($error_message)) !!}</p>
        @endforeach
    </div>
</div>
@endcan
@endisset

@endsection
