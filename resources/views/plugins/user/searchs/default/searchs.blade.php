{{--
 * 検索画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($searchs_frame->id)
    @include('plugins.user.searchs.default.searchs_form')
@else
    @can('frames.edit',[[null, $frame->plugin_name, $buckets]])
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        検索設定を作成してください。
    </div>
    @endcan
@endif
@endsection
