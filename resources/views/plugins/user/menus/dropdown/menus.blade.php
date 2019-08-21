{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@if ($pages)
<div class="nav-tabs-menu">
    <ul class="nav nav-tabs nav-justified hidden-xs" style="">
    @foreach($pages as $page_obj)

        {{-- 非表示のページは対象外 --}}
        @if ($page_obj->display_flag == 1)

            {{-- parent_idがnullのものを第一階層ページとして、最初のリストの対象とする --}}
            @if ($page_obj->parent_id == null)

                {{-- カレントページ、もしくは自分が親の場合の処理 --}}
                @if ($page_obj->id == $page_id || $page->isDescendantOf($page_obj))

                    {{-- 子供のページがある場合 --}}
                    @if (count($page_obj->children) > 0)

                        {{-- カレント --}}
                        @if ($page_obj->id == $page_id)
                        <li role="presentation" class="active dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="background-color: #3097d1; color: #ffffff;">
                        @else
                        <li role="presentation" class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        @endif

                                {{$page_obj->page_name}}
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">

                                {{-- 自分へのリンク（ドロップダウンでリンクができなくなるため） --}}
                                <li role="presentation"><a href="{{ url("$page_obj->permanent_link") }}">{{$page_obj->page_name}}</a></li>
                                <li role="separator" class="divider"></li>

                                {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                                @include('plugins.user.menus.dropdown.menu_children',['children' => $page_obj->children[0]])
                            </ul>
                        </li>
                    @else
                        <li role="presentation" class="active">
                            <a href="{{ url("$page_obj->permanent_link") }}" style="background-color: #3097d1; color: #ffffff;">
                                {{$page_obj->page_name}}
                            </a>
                        </li>
                    @endif
                @else
                    <li role="presentation">
                        <a href="{{ url("$page_obj->permanent_link") }}">
                            {{$page_obj->page_name}}
                                @if (count($page_obj->children) > 0)
                                <span class="caret"></span>
                            @endif
                        </a>
                    </li>
                @endif
            @endif
        @endif
    @endforeach
    </ul>
</div>
@endif
