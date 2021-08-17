{{--
 * カテゴリテンプレート
 *
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Linklistsプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.linklists.linklists_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ワーニングメッセージ --}}
@if (empty($linklist) || empty($linklist->id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> {{ __('messages.empty_bucket_setting', ['plugin_name' => $frame->plugin_name_full]) }}
    </div>
@else

    {{-- カテゴリ設定画面 --}}
    @include('plugins.common.user_list_category')

@endif
@endsection
