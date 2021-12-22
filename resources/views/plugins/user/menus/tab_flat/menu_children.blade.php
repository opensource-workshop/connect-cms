{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

{{-- 自階層で非表示のページは対象外 --}}
@if ($children->isView(Auth::user(), false, true, $page_roles))
    @if ($active_page_id == $children->id)
        <li role="presentation" class="nav-item {{'depth-' . $page->depth}} {{$page->getClass()}}"><a href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!} class="nav-link active">{{$children->page_name}}</a></li>
    @else
        <li role="presentation" class="nav-item {{'depth-' . $page->depth}} {{$page->getClass()}}"><a href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!} class="nav-link">{{$children->page_name}}</a></li>
    @endif
@endif

{{-- 子要素を再帰的に表示 --}}
@if ($children->children && count($children->children) > 0)
    @foreach($children->children as $grandchild)
        @include('plugins.user.menus.tab_flat.menu_children',['children' => $grandchild, 'active_page_id' => $active_page_id])
    @endforeach
@endif
