{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)
<nav aria-label="サイトマップ">

    @foreach($pages as $page)

        {{-- 子供のページがある場合 --}}
        @if (count($page->children) > 0)
<ul>
            {{-- 非表示のページは対象外(非表示の判断はこのページのみで、子のページはそのページでの判断を行う) --}}
            @if ($page->isView(Auth::user(), false, true, $page_roles))
<li>
                {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                @if ($page->id == $page_id)
                <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="{{ 'depth-' . $page->depth }} active">
                @else
                <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="{{ 'depth-' . $page->depth }}">
                @endif
                    {{-- 各ページの深さをもとにインデントの表現 --}}
                    @for ($i = 0; $i < $page->depth; $i++)
                        @if ($i+1==$children->depth && $menu) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
                    @endfor
                    {{$page->page_name}}
                </a>

                {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                @foreach($page->children as $children)
                    @include('plugins.user.menus.sitemap.menu_children',['children' => $children, 'page_id' => $page_id])
                @endforeach
</li>
            @endif
</ul>
        @else

            {{-- 非表示のページは対象外 --}}
            @if ($page->isView(Auth::user(), false, true, $page_roles))
<ul>
<li>
                {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                @if ($page->id == $page_id)
                <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="active">
                @else
                <a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="">
                @endif
                    {{-- 各ページの深さをもとにインデントの表現 --}}
                    @for ($i = 0; $i < $page->depth; $i++)
                        @if ($i+1==$children->depth && $menu) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
                    @endfor
                    {{$page->page_name}}
                </a>
</li>
</ul>
            @endif
        @endif
    @endforeach

</nav>
@endif
@endsection
