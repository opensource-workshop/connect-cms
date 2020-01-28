{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@php
    // 下層ページに表示ONのものがあれば、カレントページに ＋ や ー を表示するため、
    // 下層ページに表示ONのものがあるかチェックするための関数
    $display_check = function ($pages) use(&$display_check) {
        $child_on = false;
        foreach ($pages as $page) {
            if ($page->children) {
                $child_on = $display_check($page->children);
            }
            if ($page->display_flag) {
                $child_on = true;
                return $child_on;
            }
        }
        return $child_on;
    };
@endphp

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

    {{-- 子ページがある＆子ページに表示ON のページがある --}}
    @if ($children->children && count($children->children) > 0 && $display_check($children->children))

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

        {{-- カレントもしくは自分のルート筋なら子ページを再帰的に表示する --}}
        @if ($children->isAncestorOf($current_page) || $current_page->id == $children->id)

            @foreach($children->children as $grandchild)
                @include('plugins.user.menus.opencurrenttree.menu_children',['children' => $grandchild])
            @endforeach
        @endif
    @endif
@endif
