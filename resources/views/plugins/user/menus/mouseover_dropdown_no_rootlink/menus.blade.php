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
    <nav aria-label="タブメニュー">
    <ul class="nav nav-tabs nav-justified d-none d-md-flex">
    @foreach($pages as $page_obj)

        {{-- 非表示のページは対象外 --}}
        @if ($page_obj->isView(Auth::user(), false, true, $page_roles))

            {{-- 子供のページがあり、表示する子ページがある場合 --}}
            @if (count($page_obj->children) > 0 && $page->existChildrenPagesToDisplay($page_obj->children))

                <li class="nav-item dropdown {{$page_obj->getClass()}}" onmouseleave="$(this).find('a.nav-link').click();$(this).find('a.nav-link').blur();">
                {{-- カレント --}}
                @if ($ancestors->contains('id', $page_obj->id))
                    <a class="nav-link active dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();" aria-current="page">
                @else
                    <a class="nav-link dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();">
                @endif

                        {{$page_obj->page_name}}
                        <span class="caret"></span>
                    </a>
                    <div class="dropdown-menu">

                        {{-- 自分へのリンクなし --}}
                        {{--
                        <a class="dropdown-item" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>{{$page_obj->page_name}}</a>
                        --}}
                        <span class="dropdown-item">{{$page_obj->page_name}}</span>
                        <div class="dropdown-divider"></div>

                        {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                        @foreach($page_obj->children as $children)
                            @include('plugins.user.menus.mouseover_dropdown_no_rootlink.menu_children',['children' => $children])
                        @endforeach
                    </div>
                </li>
            @else
                <li class="nav-item {{$page_obj->getClass()}}">
                        {{-- カレント --}}
                    @if ($ancestors->contains('id', $page_obj->id))
                    <a class="nav-link text-nowrap active" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} aria-current="page">
                    @else
                    <a class="nav-link text-nowrap" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>
                    @endif
                        {{$page_obj->page_name}}
                    </a>
                </li>
            @endif
        @endif
    @endforeach
    </ul>
    </nav>
@endif
@endsection
