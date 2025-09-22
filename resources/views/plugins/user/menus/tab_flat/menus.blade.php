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
@include('plugins.user.menus.common.edit_button')
@if ($pages)

    @php $active_page_id = \App\Models\Common\Page::getTabFlatActivePageId($pages, $ancestors, $page_roles); @endphp

    <nav aria-label="タブメニュー">
    <ul class="nav nav-tabs nav-justified d-none d-md-flex">
    @foreach($pages as $page)

        {{-- 自階層ページの表示非表示 --}}
        @if ($page->isView(Auth::user(), false, true, $page_roles))
            @if ($active_page_id == $page->id)
                <li role="presentation" class="nav-item text-nowrap {{'depth-' . $page->depth}} {{$page->getClass()}}"><a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="nav-link active">{{$page->page_name}}</a></li>
            @else
                <li role="presentation" class="nav-item text-nowrap {{'depth-' . $page->depth}} {{$page->getClass()}}"><a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="nav-link">{{$page->page_name}}</a></li>
            @endif
        @endif

        {{-- 下階層の表示非表示処理のため、自階層の表示非表示は display_flag を考慮せずチェック --}}
        @if ($page->isView(Auth::user(), true, true, $page_roles))
            {{-- 子供のページがある場合 --}}
            @if (count($page->children) > 0)
                {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                @foreach($page->children as $children)
                    @include('plugins.user.menus.tab_flat.menu_children',['children' => $children, 'page_id' => $page_id, 'active_page_id' => $active_page_id])
                @endforeach
            @endif
        @endif

    @endforeach
    </ul>
    </nav>
@endif
@endsection
