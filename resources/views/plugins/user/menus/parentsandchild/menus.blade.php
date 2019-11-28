{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

@if ($pages)
<div class="list-group" style="margin-bottom: 0;">
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
    @if (count($pages[$index]->children) > 0)
        @php
            $tmp_page[] = $pages[$index];
            $pages=$tmp_page;
        @endphp
        @foreach($pages as $page_obj)
            @if($page_obj->parent_id == null)
                {{-- 非表示のページは対象外 --}}
                @if ($page_obj->display_flag == 1)
                        <a href="{{ url("$page_obj->permanent_link") }}" class="list-group-item">{{$page_obj->page_name}}</a>
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
