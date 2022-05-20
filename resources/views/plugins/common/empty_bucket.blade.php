{{--
 * バケツなし画面テンプレート。
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- バケツなし --}}
@can('frames.edit', [[null, null, null, $frame]])
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">{{ __('messages.empty_bucket', ['plugin_name' => $frame->plugin_name_full]) }}</p>
        </div>
    </div>
@endcan

@endsection
