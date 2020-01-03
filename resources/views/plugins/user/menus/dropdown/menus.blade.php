{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contsnts_$frame->id")
@if ($pages)
    <ul class="nav nav-tabs nav-justified d-none d-md-flex" style="">
    @foreach($pages as $page_obj)

        {{-- 非表示のページは対象外 --}}
        @if ($page_obj->display_flag == 1)

            {{-- カレントページ、もしくは自分が親の場合の処理 --}}
            @if ($page_obj->id == $page_id || $page->isDescendantOf($page_obj))

                {{-- 子供のページがある場合 --}}
                @if (count($page_obj->children) > 0)

                    <li class="nav-item dropdown">
                    {{-- カレント --}}
                    @if ($page_obj->id == $page_id)
                        <a class="nav-link active dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    @else
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    @endif

                            {{$page_obj->page_name}}
                            <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu">

                            {{-- 自分へのリンク（ドロップダウンでリンクができなくなるため） --}}
                            <a class="dropdown-item" href="{{ url("$page_obj->permanent_link") }}">{{$page_obj->page_name}}</a>
                            <div class="dropdown-divider"></div>

                            {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                            @foreach($page_obj->children as $children)
                                @include('plugins.user.menus.dropdown.menu_children',['children' => $children])
                            @endforeach
                        </div>
                    </li>
                @else
                    <li class="nav-item">
                    @if ($page_obj->id == $page_id)
                        <a class="nav-link active" href="{{ url("$page_obj->permanent_link") }}">
                    @else
                        <a class="nav-link" href="{{ url("$page_obj->permanent_link") }}">
                    @endif
                            {{$page_obj->page_name}}
                        </a>
                    </li>
                @endif
            @else
                {{-- 子供のページがある場合 --}}
                @if (count($page_obj->children) > 0)
                    <li class="nav-item dropdown">
                    @if ($page_obj->id == $page_id)
                        <a class="nav-link dropdown-toggle active" href="{{ url("$page_obj->permanent_link") }}">
                    @else
                        <a class="nav-link dropdown-toggle" href="{{ url("$page_obj->permanent_link") }}">
                    @endif
                @else
                    <li class="nav-item">
                    @if ($page_obj->id == $page_id)
                        <a class="nav-link active" href="{{ url("$page_obj->permanent_link") }}">
                    @else
                        <a class="nav-link" href="{{ url("$page_obj->permanent_link") }}">
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
@endif
@endsection
