{{--
 * メニュー（ハンバーガーメニュー）表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 牧野　可也子 <makino@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)

{{-- ハンバーガーメニュー用script&CSS読み込み --}}
@include('plugins.user.menus.hamburger_button.menus_script')

{{-- ハンバーガーメニュー --}}
<div class="menu-humburger-button">
    <a class="menu-humburger-link" href="#">
        <p></p>
        <p></p>
        <p></p>
    </a>
</div>

{{-- メニュー本体 --}}
<div class="hamburger-menu-area">
<div class="list-group mb-0 hamburger-menu" role="navigation" aria-label="メニュー">
    @include('plugins.user.menus.opencurrenttree.menu_parent')
</div>
</div>
@endif
@endsection
