{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@if ($children->isView(Auth::user()))

    @if ($children->id == $page_id)
    <a href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!} class="list-group-item active">
    @else
    <a href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!} class="list-group-item">
    @endif
        {{-- 各ページの深さをもとにインデントの表現 --}}
        @for ($i = 0; $i < $children->depth; $i++)
            @if ($i+1==$children->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
        @endfor
        {{$children->page_name}}
    </a>

    @if ($children->children && count($children->children) > 0)
        @foreach($children->children as $grandchild)
            @include('plugins.user.menus.default.menu_children',['children' => $grandchild])
        @endforeach
{{--
        @include('plugins.user.menus.default.menu_children',['children' => $children->children[0]])
--}}
    @endif
@endif
