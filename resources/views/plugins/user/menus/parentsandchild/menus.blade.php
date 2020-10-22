{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)
<div class="list-group mb-0" role="navigation" aria-label="メニュー">
    @foreach($pages as $key => $page)
        @php
            if (isset($index)) {
                break;
            }
            if ($ancestors[0]->id == $page->id) {
                $index = $key;
            }
        @endphp
    @endforeach

    {{-- 子供のページがある場合 --}}
    @if (isset($index) && count($pages[$index]->children) > 0)
        @php
            $tmp_page[] = $pages[$index];
            $pages=$tmp_page;
        @endphp
        @foreach($pages as $page_obj)
            @if($page_obj->parent_id == null)
                {{-- 非表示のページは対象外 --}}
                @if ($page_obj->isView(Auth::user(), false, true, $page_roles))
                    @if ($page_obj->id == $page_id)
                    <a href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} class="list-group-item {{ 'depth-' . $page_obj->depth }} active" aria-current="page">{{$page_obj->page_name}}</a>
                    @else
                    <a href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} class="list-group-item {{ 'depth-' . $page_obj->depth }}">{{$page_obj->page_name}}</a>
                    @endif
                    @if (isset($page_obj->children))
                        {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                        @include('plugins.user.menus.parentsandchild.menu_children',['children' => $page_obj->children])
                    @endif
                @endif
            @endif
        @endforeach
    @endif
</div>
@endif
@endsection
