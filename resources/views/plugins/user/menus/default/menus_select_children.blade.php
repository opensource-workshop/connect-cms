{{--
 * メニューの子要素表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

{{-- 設定画面では、全てのページを表示して、選択可能とする。
@if ($children->display_flag == 1)
--}}

    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="page_select{{$children->id}}" name="page_select[]" value="{{$children->id}}" @if ($menu && $menu->onPage($children->id)) checked @endif />
        <label class="custom-control-label" for="page_select{{$children->id}}">

        {{-- 各ページの深さをもとにインデントの表現 --}}
        @for ($i = 0; $i < $children->depth; $i++)
            @if ($i+1==$children->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
        @endfor
        {{$children->page_name}}
        </label>
    </div>

    @if ($children->children && count($children->children) > 0)
        @foreach($children->children as $grandchild)
            @include('plugins.user.menus.default.menus_select_children',['children' => $grandchild])
        @endforeach
{{--
        @include('plugins.user.menus.default.menus_select_children',['children' => $children->children[0]])
--}}
{{--
    @endif
--}}
@endif
