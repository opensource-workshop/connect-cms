{{--
 * エラーメッセージのテンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@php
    $can_show_error = false;
    if (Auth::check()) {
        $can_show_error = Auth::user()->can('frames.edit', [[null, null, null, $frame]])
            || Auth::user()->can('posts.update', [[null, $frame->plugin_name, $buckets, $frame]])
            || Auth::user()->can('posts.create', [[null, $frame->plugin_name, $buckets, $frame]]);
    }
@endphp

@if ($can_show_error)
    <div class="card border-danger">
        <div class="card-body">
            @foreach ($error_messages as $error_message)
                <p class="text-center cc_margin_bottom_0">{!! nl2br(e($error_message)) !!}</p>
            @endforeach
        </div>
    </div>
@endif
@endsection
