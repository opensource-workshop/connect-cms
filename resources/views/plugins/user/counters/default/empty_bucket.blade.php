{{--
 * カウンター・バケツなし画面テンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- バケツなし --}}
@can('frames.edit',[[null, null, null, $frame]])
<div class="card border-danger">
    <div class="card-body">
        <p class="text-center cc_margin_bottom_0">{{ __('messages.empty_bucket', ['plugin_name' => 'カウンター']) }}</p>
    </div>
</div>
@endcan

@endsection
