{{--
 * 検索画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contsnts_$frame->id")
@if(isset($searchs_frame))
    @include('plugins.user.searchs.default.searchs_form')
@else
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        検索設定を作成してください。
    </div>
@endif
@endsection
