{{--
 * エラーメッセージのテンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- xmlのパースエラー --}}
@isset($parse_errors)
    <div class="alert alert-danger" role="alert">
        {{ $parse_errors['message'] }}
        @can('frames.edit',[[null, null, null, $frame]])
            @isset($parse_errors['url'])
                <ul>
                    @foreach ($parse_errors['url'] as $url)
                        <li>{{ $url }}</li>
                    @endforeach
                </ul>
            @endisset
        @endcan
    </div>
@endisset
{{-- 管理者専用のエラーメッセージ --}}
@can('frames.edit',[[null, null, null, $frame]])
    @isset($error_messages)
        <div class="card border-danger">
            <div class="card-body">
                @foreach ($error_messages as $error_message)
                    <p class="text-center cc_margin_bottom_0">{!! nl2br(e($error_message)) !!}</p>
                @endforeach
            </div>
        </div>
    @endisset
@endcan
@endsection
