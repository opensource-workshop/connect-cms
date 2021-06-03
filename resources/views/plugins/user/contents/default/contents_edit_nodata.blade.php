{{--
 * 編集画面(データがなかった場合の)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.contents.contents_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
{{-- データ --}}
<div class="card">
    <div class="card-body">
        コンテンツ・データが登録されていません。
    </div>
</div>
@endsection
