{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

{{-- 非表示のページは対象外 --}}
@if ($page_obj->isView(Auth::user(), false, true, $page_roles))

    {{-- 子供のページがあり、表示する子ページがある場合 --}}
    @if (count($page_obj->children) > 0 && $page->existChildrenPagesToDisplay($page_obj->children))

        <li class="nav-item icon_menu_main_list dropdown {{$page_obj->getClass()}}" onmouseleave="$(this).find('a.nav-link').click();$(this).find('a.nav-link').blur();">
        {{-- カレント --}}
        @if ($page_obj->id == $page_id)
            <a class="nav-link active dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();" aria-current="page">
        @else
            <a class="nav-link dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();">
        @endif

                <span class="d-md-block d-none">{{$page_obj->page_name}}</span>
                <span class="caret"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <ul class="icon_menu_second_list">

                {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                    @foreach($page_obj->children as $children)
                        @include('plugins.user.menus.mouseover_dropdown_no_rootlink_for_icon.menu_children',['children' => $children])
                    @endforeach
                </ul>
            </div>
        </li>
    @else
        <li class="nav-item icon_menu_main_list active {{$page_obj->getClass()}}">
            <a class="nav-link text-nowrap" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>
                <span class="d-md-block d-none">{{$page_obj->page_name}}</span>
            </a>
        </li>
    @endif
@endif
