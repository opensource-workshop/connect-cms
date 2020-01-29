{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@if ($children->display_flag == 1)

    @if ($children->id == $page_id)
    <a class="dropdown-item active" href="{{ url("$children->permanent_link") }}">
    @else
    <a class="dropdown-item" href="{{ url("$children->permanent_link") }}">
    @endif

        {{-- 各ページの深さをもとにインデントの表現 --}}
        @for ($i = 0; $i < $children->depth; $i++)
            @if ($i+1==$children->depth) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
        @endfor
        {{$children->page_name}}
    </a>
    @if ($children->children && count($children->children) > 0)
        @foreach($children->children as $ckey => $depth_children)
            @include('plugins.user.menus.mouseover_dropdown_no_root.menu_children',['children' => $children->children[$ckey]])
        @endforeach
    @endif
@endif
