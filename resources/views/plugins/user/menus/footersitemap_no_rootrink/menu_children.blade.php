{{--
 * メニューの子要素表示画面
 *
 * @param obj $children ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
<ul>
    @foreach($children as $page_obj)
        @if ($page_obj->display_flag == 1)
            <li>
                <a href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!}>{{$page_obj->page_name}}</a>
                @if (isset($page_obj->children))
                    @include('plugins.user.menus.footersitemap.menu_children',['children' => $page_obj->children])
                @endif
            </li>
        @endif
    @endforeach
</ul>
