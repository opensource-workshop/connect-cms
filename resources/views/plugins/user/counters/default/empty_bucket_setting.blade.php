{{--
 * カウンター設定・バケツなし画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.counters.counters_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- バケツなし --}}
<div class="alert alert-warning">
    <i class="fas fa-exclamation-circle"></i> {{ __('messages.empty_bucket_setting', ['plugin_name' => 'カウンター']) }}
</div>

@endsection
