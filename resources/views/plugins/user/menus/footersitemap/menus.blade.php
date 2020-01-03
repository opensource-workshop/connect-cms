{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contsnts_$frame->id")
@if ($pages)
<div class="footersitemap">
    <ul class="nav nav-justified">
    @foreach($pages as $page_obj)
        @if($page_obj->parent_id == null)
            {{-- 非表示のページは対象外 --}}
            @if ($page_obj->display_flag == 1)
                <li class="nav-item">
                    <a href="{{ url("$page_obj->permanent_link") }}">{{$page_obj->page_name}}</a>
                    @if (isset($page_obj->children))
                        {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                        @include('plugins.user.menus.footersitemap.menu_children',['children' => $page_obj->children])
                    @endif
                </li>
            @endif
        @endif
    @endforeach
    </ul>
</div>
@endif
@endsection
