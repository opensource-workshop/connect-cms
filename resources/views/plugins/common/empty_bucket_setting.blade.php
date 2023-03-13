{{--
 * 設定画面のバケツなしテンプレート。
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
 *
 * @param $plugin_name   プラグイン名
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @includeFirst([
        'plugins.user.' . $frame->plugin_name . '.' . $frame->plugin_name . '_frame_edit_tab',
        'plugins_option.user.' . $frame->plugin_name . '.' . $frame->plugin_name . '_frame_edit_tab'
    ])
@endsection

@section("plugin_setting_$frame->id")

@php
$plugin_name = $plugin_name ?? $frame->plugin_name_full;
@endphp

{{-- バケツなし --}}
<div class="alert alert-warning">
    <i class="fas fa-exclamation-circle"></i> {{ __('messages.empty_bucket_setting', ['plugin_name' => $plugin_name]) }}
</div>

@endsection
