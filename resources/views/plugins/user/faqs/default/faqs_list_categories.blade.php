{{--
 * カテゴリテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Faqプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.faqs.faqs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ワーニングメッセージ --}}
@if (empty($faq_frame) || empty($faq_frame->faqs_id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> 設定画面から、使用するFAQを選択するか、作成してください。
    </div>
@else

    {{-- カテゴリ設定画面 --}}
    @include('plugins.common.user_list_category')

@endif
@endsection
