{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@php
function genMultiLevelChildren($page){
    $ret_children = [];
    if(isset($page->children[0])){
        foreach($page->children as $children){
            $tmp = [
                        'id' => $children->id,
                        'page_name' => $children->page_name,
                        'permanent_link' => $children->permanent_link,
                        'display_flag' => $children->display_flag,
                        'depth' => $children->depth,
            ];
            // まだ下に階層がある場合
            if(isset($children->children[0])){
                $tmp['children'] = genMultiLevelChildren($children);
            }
            $ret_children[] = $tmp; 
        }
    }
    return $ret_children;
}
if (!$pages) {
    reutrn;
}
$sitemap = [];
foreach($pages as $page){
    // 第一階層の配列のみでOK
    if($page->parent_id == null){
        $sitemap[] = [
                        'id' => $page->id,
                        'page_name' => $page->page_name,
                        'permanent_link' => $page->permanent_link,
                        'display_flag' => $page->display_flag,
                        'children' => genMultiLevelChildren($page)
        ];
        continue;
    }
}
// オブジェクトにする
$pages = json_decode(json_encode($sitemap), FALSE);
@endphp

@if ($pages)
<div class="footersitemap">
    <ul class="nav nav-justified">
    @foreach($pages as $page_obj)
        {{-- 非表示のページは対象外 --}}
        @if ($page_obj->display_flag == 1)
            <li>
                <a href="{{ url("$page_obj->permanent_link") }}">{{$page_obj->page_name}}</a>
                @if (isset($page_obj->children))
                    {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                    @include('plugins.user.menus.footersitemap.menu_children',['children' => $page_obj->children])
                @endif
            </li>
        @endif
    @endforeach
    </ul>
</div>
@endif
