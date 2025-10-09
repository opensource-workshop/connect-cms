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
@include('plugins.user.menus.common.edit_button')
@if ($pages)

    <div class="list-group mb-0" role="navigation" aria-label="メニュー">
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
                </a>

                {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                @foreach($page->children as $children)
                    @include('plugins.user.menus.default.menu_children',['children' => $children, 'page_id' => $page_id])
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
    </div>
@endif
@endsection
