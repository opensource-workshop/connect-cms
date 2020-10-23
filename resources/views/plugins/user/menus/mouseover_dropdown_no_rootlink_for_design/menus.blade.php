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

            {{-- 子供のページがある場合 --}}
            @if (count($page_obj->children) > 0)

                <li class="nav-item dropdown {{$page_obj->getClass()}}" onmouseleave="$(this).find('a.nav-link').click();$(this).find('a.nav-link').blur();">
                {{-- カレント --}}
                @if ($page_obj->id == $page_id)
                    <a class="nav-link active dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();" aria-current="page">
                @else
                    <a class="nav-link dropdown-toggle {{ 'depth-' . $page_obj->depth }}" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();">
                @endif

                        {{$page_obj->page_name}}
                        <span class="caret"></span>
                    </a>
                    <div class="dropdown-menu">
                        <ul>

                        {{-- 自分へのリンク（ドロップダウンでリンクができなくなるため） --}}
{{--
                        <a class="dropdown-item" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>{{$page_obj->page_name}}</a>
                        <div class="dropdown-divider"></div>
--}}
                        {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                            @foreach($page_obj->children as $children)
                                @include('plugins.user.menus.mouseover_dropdown_no_rootlink_for_design.menu_children',['children' => $children])
                            @endforeach
                        </ul>
                    </div>
                </li>
            @else
                <li class="nav-item active {{$page_obj->getClass()}}">
                    <a class="nav-link" href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>
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
