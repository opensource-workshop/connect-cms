{{--
 * メニューの子要素表示画面
 *
 * @param obj $children ページデータの配列
 * @author 牧野　可也子 <makino@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@if ($children->isView(Auth::user(), false, true, $page_roles))
<li>
    @if ($children->id == $page_id)
    <a class="dropdown-item {{ 'depth-' . $children->depth }} active" href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!} aria-current="page">
    @else
    <a class="dropdown-item {{ 'depth-' . $children->depth }}" href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!}>
    @endif

        {{-- 各ページの深さをもとにインデントの表現 --}}
        @for ($i = 0; $i < $children->depth; $i++)
            @if ($i+1==$children->depth && $menu) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
        @endfor
        {{$children->page_name}}
    </a>
    @if ($children->children && count($children->children) > 0)
        <ul>
            @foreach($children->children as $ckey => $depth_children)
                @include('plugins.user.menus.mouseover_dropdown_no_rootlink_for_icon.menu_children',['children' => $children->children[$ckey]])
            @endforeach
        </ul>
    @endif
</li>
@endif
