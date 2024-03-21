{{--
 * カテゴリテンプレート
 *
 * @author 石垣　佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベースプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ワーニングメッセージ --}}
@if (empty($database_frame) || empty($database_frame->databases_id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> 設定画面から、使用するデータベースを選択するか、作成してください。
    </div>
@else

    {{-- カテゴリ設定画面 --}}
    @include('plugins.common.user_list_category')

@endif
@endsection
