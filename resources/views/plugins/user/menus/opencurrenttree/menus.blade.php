{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)
    <div class="list-group mb-0" role="navigation" aria-label="メニュー">
        @include('plugins.user.menus.opencurrenttree.menu_parent')
    </div>
@endif
@endsection
