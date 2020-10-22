{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)
<div class="footersitemap" role="navigation" aria-label="サイトマップ">
    <ul class="nav nav-justified">
    @foreach($pages as $page_obj)
        @if($page_obj->parent_id == null)
            {{-- 非表示のページは対象外 --}}
            @if ($page_obj->isView(Auth::user(), false, true, $page_roles))
                <li class="nav-item">
                    <a href="#" onclick="return false" class="cc-cursor-text">{{$page_obj->page_name}}</a>
                    @if (isset($page_obj->children))
                        {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                        @include('plugins.user.menus.footersitemap_no_rootrink.menu_children',['children' => $page_obj->children])
                    @endif
                </li>
            @endif
        @endif
    @endforeach
    </ul>
</div>
@endif
@endsection
