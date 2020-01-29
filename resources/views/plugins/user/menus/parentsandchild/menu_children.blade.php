{{--
 * メニューの子要素表示画面
 *
 * @param obj $children ページデータの配列
 * @author 堀口 <horiguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
    @foreach($children as $page_obj)
        @if ($page_obj->isView(Auth::user()))

            @if ($page_obj->id == $page_id)
            <a href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} class="list-group-item active">
            @else
            <a href="{{$page_obj->getUrl()}}" {!!$page_obj->getUrlTargetTag()!!} class="list-group-item">
            @endif

            {{-- 各ページの深さをもとにインデントの表現 --}}
            @for ($i = 0; $i < $page_obj->depth; $i++)
                @if ($i+1==$page_obj->depth) {!!$menu->getIndentFont()!!} @else <span class="px-2"></span>@endif
            @endfor

            {{$page_obj->page_name}}</a>

            @if (isset($page_obj->children))
                @include('plugins.user.menus.parentsandchild.menu_children',['children' => $page_obj->children])
            @endif

        @endif
    @endforeach
