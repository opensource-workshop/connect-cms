{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@if ($children->isView())

    @if ($children->id == $page_id)
    <a href="{{ url("$children->permanent_link") }}" class="list-group-item active">
    @else
    <a href="{{ url("$children->permanent_link") }}" class="list-group-item">
    @endif
        {{-- 各ページの深さをもとにインデントの表現 --}}
        @for ($i = 0; $i < $children->depth; $i++)
            @if ($i+1==$children->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
        @endfor
    @if ($children->children && count($children->children) > 0)

        {{-- カレントもしくは自分のルート筋なら表示する --}}
        @if ($children->isAncestorOf($current_page) || $current_page->id == $children->id)
            {{$children->page_name}} <i class="fas fa-minus"></i>
        @else
            {{$children->page_name}} <i class="fas fa-plus"></i>
        @endif

    @else
        {{$children->page_name}}
    @endif
    </a>

    @if ($children->children && count($children->children) > 0)

        {{-- カレントもしくは自分のルート筋なら表示する --}}
        @if ($children->isAncestorOf($current_page) || $current_page->id == $children->id)

            @foreach($children->children as $grandchild)
                @include('plugins.user.menus.opencurrenttree.menu_children',['children' => $grandchild])
            @endforeach
        @endif
    @endif
@endif
