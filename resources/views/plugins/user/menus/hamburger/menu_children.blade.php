{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author horiguchi masayuki <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@if ($children->isView(Auth::user(), false, true, $page_roles))
        <li class="nav-item">
            {{-- 下層ページに飛ばす時 --}}
            @if ($children->transfer_lower_page_flag)
                <a class="hamburger-accordion-block {{ 'depth-' . $children->depth }}" aria-controls="accordion-{{$children->id}}" href="#accordion-{{$children->id}}"  data-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-chevron-right"></i>
                    {{$children->page_name}}
                </a>
                <ul id="accordion-{{$children->id}}" class="navbar-nav ml-3 collapse">
                    @foreach($children->children as $grandchild)
                        @include('plugins.user.menus.hamburger.menu_children',['children' => $grandchild, 'page_id' => $page_id, 'parent_id' => $children->id])
                    @endforeach
                </ul>
            @else
                {{-- 下層ページに飛ばさない時 --}}
                @if ($children->id == $page_id)
                    <a class="nav-link {{ 'depth-' . $children->depth }} active" href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!}>
                @else
                    <a class="nav-link {{ 'depth-' . $children->depth }}" href="{{$children->getUrl()}}" {!!$children->getUrlTargetTag()!!}>
                @endif
                        <i class="fas fa-chevron-right"></i>
                        {{$children->page_name}}
                    </a>
                    @if ($children->children && count($children->children) > 0)
                        {{-- 子階層がある場合にはアコーディオンを付与 --}}
                        <a class="hamburger-accordion" aria-controls="accordion-{{$children->id}}" href="#accordion-{{$children->id}}"  data-toggle="collapse" aria-expanded="false"></a>
                        <ul id="accordion-{{$children->id}}" class="navbar-nav ml-3 collapse">
                        @foreach($children->children as $grandchild)
                            @include('plugins.user.menus.hamburger.menu_children',['children' => $grandchild, 'page_id' => $page_id, 'parent_id' => $children->id])
                        @endforeach
                        </ul>
                    @endif
            @endif
        </li>
@endif