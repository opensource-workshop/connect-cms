{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@if ($children->isView(Auth::user(), false, true, $page_roles))
<ul>
<li>
    @if ($children->id == $page_id)
    <a href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!} class="{{ 'depth-' . $children->depth }} active">
    @else
    <a href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!} class="{{ 'depth-' . $children->depth }}">
    @endif
        {{-- 各ページの深さをもとにインデントの表現 --}}
{{--
        @for ($i = 0; $i < $children->depth; $i++)
            @if ($i+1==$children->depth && $menu) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
        @endfor
--}}
        {{$children->page_name}}
    </a>

    @if ($children->children && count($children->children) > 0)
        @foreach($children->children as $grandchild)
            @include('plugins.user.menus.sitemap.menu_children',['children' => $grandchild])
        @endforeach
    @endif
</li>
</ul>
@endif
