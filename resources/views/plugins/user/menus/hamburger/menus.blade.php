{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author horiguchi masayuki <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)
{{-- スマホのときだけ表示させる --}}
<div class="hamburger-menu d-md-none">
    <nav class="navbar navbar-expand-md navbar-light bg-main">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#grobalNav" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="grobalNav" class="collapse navbar-collapse">
                @foreach($pages as $page)
                    {{-- 子供のページがある場合 --}}
                    @if (count($page->children) > 0)
                        <ul class="navbar-nav ml-auto">
                            @if ($page->isView(Auth::user(), false, true, $page_roles))
                                <li class="nav-item">

                                {{-- 下層ページに飛ばす時 --}}
                                @if ($page->transfer_lower_page_flag)
                                        {{-- 子どもの階層がある場合にはアイコンを付与 --}}
                                        <a class="hamburger-accordion-block {{ 'depth-' . $page->depth }}" aria-controls="accordion-{{$page->id}}" href="#accordion-{{$page->id}}"  data-toggle="collapse" aria-expanded="false">
                                            {{$page->page_name}}
                                        </a>
                                        {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                                        <ul id="accordion-{{$page->id}}" class="navbar-nav ml-3 collapse">
                                        @foreach($page->children as $children)
                                            @include('plugins.user.menus.hamburger.menu_children',['children' => $children, 'page_id' => $page_id, 'parent_id' => $page->id])
                                        @endforeach
                                        </ul>
                                @else
                                    {{-- 下層ページに飛ばさない設定の時 --}}
                                    @if ($page->id == $page_id)
                                        <a class="nav-link {{ 'depth-' . $page->depth }} active" href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!}>
                                    @else
                                        <a class="nav-link {{ 'depth-' . $page->depth }}" href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!}>
                                    @endif
                                            {{$page->page_name}}
                                        </a>
                                        {{-- 子どもの階層がある場合にはアイコンを付与 --}}
                                        <a class="hamburger-accordion" aria-controls="accordion-{{$page->id}}" href="#accordion-{{$page->id}}"  data-toggle="collapse" aria-expanded="false"></a>
                                        {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                                        <ul id="accordion-{{$page->id}}" class="navbar-nav ml-3 collapse">
                                        @foreach($page->children as $children)
                                            @include('plugins.user.menus.hamburger.menu_children',['children' => $children, 'page_id' => $page_id, 'parent_id' => $page->id])
                                        @endforeach
                                        </ul>
                                @endif
                                </li>
                            @endif
                        </ul>
                    @else
                        {{-- 非表示のページは対象外 --}}
                        @if ($page->isView(Auth::user(), false, true, $page_roles))
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item">
                                {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                                @if ($page->id == $page_id)
                                    <a class="nav-link active" href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!}>
                                @else
                                    <a class="nav-link" href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!}>
                                @endif
                                    {{-- 各ページの深さをもとにインデントの表現 --}}
                                    @for ($i = 0; $i < $page->depth; $i++)
                                        @if ($i+1==$children->depth && $menu) 
                                            {!!$menu->getIndentFont()!!} 
                                        @else
                                            <span class="px-2"></span>
                                        @endif
                                    @endfor
                                            {{$page->page_name}}
                                    </a>
                            </li>
                        </ul>
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
    </nav>
</div>

@endif
@endsection
