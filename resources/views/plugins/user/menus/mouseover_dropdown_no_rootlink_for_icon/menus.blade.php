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
    <nav aria-label="アイコンメニュー">
    <ul class="nav nav-justified d-md-flex">
    {{-- 事前チェック。第一階層に表示できるページがあるかどうか、もしあるとした時、１つだけなのかどうか --}}
    <?php $count=0 ?>
    <?php $menu_pages=null ?>
    @foreach($pages as $page_obj)
        @if ($page_obj->isView(Auth::user(), false, true, $page_roles))
            <?php $count++ ?>
        @endif
    @endforeach
    @if ($count==0)
        {{-- 第一階層に表示できるページがない：第二階層をメイン階層として処理する --}}
        @foreach($pages as $page_obj)
            @if (count($page_obj->children) > 0 && $page->existChildrenPagesToDisplay($page_obj->children))
                @foreach($page_obj->children as $children)
                    @include('plugins.user.menus.mouseover_dropdown_no_rootlink_for_icon.menu_parent',['page_obj' => $children])
                @endforeach
            @endif
        @endforeach
    @elseif ($count==1)
        {{-- 第一階層に表示できるページが１つしかない：第一階層と第二階層を横並びで表示する（※ルーム貸し対応） --}}
        @foreach($pages as $page_obj)
            @if ($page_obj->isView(Auth::user(), false, true, $page_roles))
                {{-- 第一階層を表示 --}}
                <li class="nav-item icon_menu_main_list active {{$page_obj->getClass()}}">
                    <a class="nav-link text-nowrap" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>
                        <span class="d-md-block d-none">{{$page_obj->page_name}}</span>
                    </a>
                </li>

                {{-- 第二階層を表示 --}}
                @if (count($page_obj->children) > 0 && $page->existChildrenPagesToDisplay($page_obj->children))
                    @foreach($page_obj->children as $children)
                        @include('plugins.user.menus.mouseover_dropdown_no_rootlink_for_icon.menu_parent',['page_obj' => $children])
                    @endforeach
                @endif
            @endif
        @endforeach
    @else
        {{-- 上記以外 --}}
        @foreach($pages as $page_obj)
            @include('plugins.user.menus.mouseover_dropdown_no_rootlink_for_icon.menu_parent',['page_obj' => $page_obj])
        @endforeach
    @endif

    </ul>
    </nav>
@endif
@endsection
