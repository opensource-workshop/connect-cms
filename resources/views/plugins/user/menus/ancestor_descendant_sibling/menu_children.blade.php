{{--
 * メニューの子要素表示画面
 *
 * @param obj $children ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@foreach($children as $key => $page_obj)
    {{-- 自分 or 先祖 or 子孫 なら、表示する。(表示フラグは後で、isView でチェックされる) --}}
    @if ($page_obj->id == $current_page->id || $page_obj->isAncestorOf($current_page) || $page_obj->isDescendantOf($current_page) || $page_obj->isSiblingOf($current_page))
        {{-- 非表示のページは対象外 --}}
        @if ($page_obj->isView(Auth::user(), false, true, $page_roles))
            @if ($page_obj->id == $page_id)
            <a href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} class="list-group-item {{ 'depth-' . $page_obj->depth }} active" aria-current="page">
            @else
            <a href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} class="list-group-item {{ 'depth-' . $page_obj->depth }}">
            @endif

                {{-- 各ページの深さをもとにインデントの表現 --}}
                @for ($i = 0; $i < $page_obj->depth; $i++)
                    @if ($i+1==$page_obj->depth && $menu) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
                @endfor

                {{$page_obj->page_name}}
            </a>
        @endif
        @if (isset($page_obj->children))
            {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
            @include('plugins.user.menus.ancestor_descendant_sibling.menu_children',['children' => $page_obj->children])
        @endif
    @endif
@endforeach
