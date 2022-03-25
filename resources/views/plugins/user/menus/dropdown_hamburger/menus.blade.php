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

<nav aria-label="タブメニュー">
    <ul class="nav nav-tabs nav-justified d-none d-md-flex">
    @foreach($pages as $page_obj)

        {{-- 非表示のページは対象外 --}}
        @if ($page_obj->isView(Auth::user(), false, true, $page_roles))

            {{-- カレントページ、もしくは自分が親の場合の処理 --}}
            @if ($page_obj->id == $page_id || $page->isDescendantOf($page_obj))

                {{-- 子供のページがあり、表示する子ページがある場合 --}}
                @if (count($page_obj->children) > 0 && $page->existChildrenPagesToDisplay($page_obj->children))

                    <li class="nav-item dropdown {{$page_obj->getClass()}}">
                    {{-- カレント --}}
                    @if ($ancestors->contains('id', $page_obj->id))
                        <a class="nav-link active dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" aria-current="page">
                    @else
                        <a class="nav-link dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    @endif

                            {{$page_obj->page_name}}
                            <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu">

                            {{-- 自分へのリンク（ドロップダウンでリンクができなくなるため） --}}
                            <a class="dropdown-item" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>{{$page_obj->page_name}}</a>
                            <div class="dropdown-divider"></div>

                            {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                            @foreach($page_obj->children as $children)
                                @include('plugins.user.menus.dropdown.menu_children',['children' => $children])
                            @endforeach
                        </div>
                    </li>
                @else
                    <li class="nav-item {{$page_obj->getClass()}}">
                    @if ($ancestors->contains('id', $page_obj->id))
                        <a class="nav-link text-nowrap active" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} aria-current="page">
                    @else
                        <a class="nav-link text-nowrap" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>
                    @endif
                            {{$page_obj->page_name}}
                        </a>
                    </li>
                @endif
            @else
                {{-- 子供のページがあり、表示する子ページがある場合 --}}
                @if (count($page_obj->children) > 0 && $page->existChildrenPagesToDisplay($page_obj->children))
                    <li class="nav-item dropdown {{$page_obj->getClass()}}">
                    @if ($ancestors->contains('id', $page_obj->id))
                        <a class="nav-link dropdown-toggle active" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} aria-current="page">
                    @else
                        <a class="nav-link dropdown-toggle" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>
                    @endif
                @else
                    <li class="nav-item {{$page_obj->getClass()}}">
                    @if ($ancestors->contains('id', $page_obj->id))
                        <a class="nav-link text-nowrap active" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} aria-current="page">
                    @else
                        <a class="nav-link text-nowrap" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>
                    @endif
                @endif
                            {{$page_obj->page_name}}
                            @if (count($page_obj->children) > 0)
                                <span class="caret"></span>
                            @endif
                        </a>
                    </li>
            @endif
        @endif
    @endforeach
    </ul>
</nav>

@endif
@endsection
