{{--
 * メニュー表示画面：親要素表示
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@foreach($pages as $page)

    {{-- 子供のページがある場合 --}}
    @if (count($page->children) > 0)

        {{-- 非表示のページは対象外(非表示の判断はこのページのみで、子のページはそのページでの判断を行う) --}}
        @if ($page->isView(Auth::user(), false, true, $page_roles))

            {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
            @if ($page->id == $page_id)
            <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="list-group-item {{ 'depth-' . $page->depth }} active" aria-current="page">
            @else
            <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="list-group-item {{ 'depth-' . $page->depth }}">
            @endif
                {{-- 各ページの深さをもとにインデントの表現 --}}
                @for ($i = 0; $i < $page->depth; $i++)
                    @if ($i+1==$children->depth && $menu) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
                @endfor
                {{$page->page_name}}

                {{-- メニュー設定が生成されていて、表示する子ページがあり、カレントもしくは自分のルート筋なら＋、違えば－を表示する --}}
                @if ($menu && $page->existChildrenPagesToDisplay($page->children))
                    @if ($page->isAncestorOf($current_page) || $current_page->id == $page->id)
                        {!!$menu->getFolderCloseFont()!!}
                    @else
                        {!!$menu->getFolderOpenFont()!!}
                    @endif
                @endif
            </a>
        @endif

        {{-- カレントもしくは自分のルート筋なら表示する --}}
        @if ($page->isAncestorOf($current_page) || $current_page->id == $page->id)
            {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
            @foreach($page->children as $children)
                @include('plugins.user.menus.opencurrenttree.menu_children',['children' => $children, 'page_id' => $page_id])
            @endforeach
        @endif
    @else

        {{-- 非表示のページは対象外 --}}
        @if ($page->isView(Auth::user(), false, true, $page_roles))

            {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
            @if ($page->id == $page_id)
            <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="list-group-item active" aria-current="page">
            @else
            <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="list-group-item">
            @endif
                {{-- 各ページの深さをもとにインデントの表現 --}}
                @for ($i = 0; $i < $page->depth; $i++)
                    @if ($i+1==$children->depth && $menu) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
                @endfor
                {{$page->page_name}}
            </a>
        @endif
    @endif
@endforeach
