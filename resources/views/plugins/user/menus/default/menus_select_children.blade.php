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
        @php
            $menu_display_label = $children->display_flag ? 'メニュー表示: 表示' : 'メニュー表示: 非表示';
            if (!$children->display_flag && $children->base_display_flag == 1) {
                $menu_display_label .= '（親ページの非表示を継承）';
            }
        @endphp
        <span class="cc-menu-page-conditions js-page-condition-item ml-2 {{ $is_page_condition ? '' : 'd-none' }}">
            @if ($children->display_flag == 1)
                <i class="far fa-eye" title="{{$menu_display_label}}"></i>
            @else
                <i class="far fa-eye-slash text-muted" title="{{$menu_display_label}}"></i>
            @endif
        </span>
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
