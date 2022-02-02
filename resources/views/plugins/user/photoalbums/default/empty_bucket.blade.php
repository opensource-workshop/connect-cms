{{--
 * フォトアルバム・バケツなし画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@can('frames.edit',[[null, $frame->plugin_name, $buckets]])
{{-- バケツなし --}}
<div class="card border-danger">
    <div class="card-body">
        <p class="text-center cc_margin_bottom_0">{{ __('messages.empty_bucket', ['plugin_name' => 'フォトアルバム']) }}</p>
    </div>
</div>
@endcan

@endsection
