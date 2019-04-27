{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@if ($pages)

<ul class="nav nav-tabs nav-justified hidden-xs" style="">
@foreach($pages as $page)

    {{-- 非表示のページは対象外 --}}
    @if ($page->display_flag == 1)

        {{-- parent_idがnullのものを第一階層ページとして、最初のリストの対象とする --}}
        @if ($page->parent_id == null)

            {{-- カレントページ、もしくは自分が親の場合の処理 --}}
            @if ($page->id == $page_id || $current_page->isDescendantOf($page))

                {{-- 子供のページがある場合 --}}
                @if (count($page->children) > 0)

                    {{-- カレント --}}
                    @if ($page->id == $page_id)
                    <li role="presentation" class="active dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="background-color: #3097d1; color: #ffffff;">
                    @else
                    <li role="presentation" class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    @endif

                            {{$page->page_name}}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">

                            {{-- 自分へのリンク（ドロップダウンでリンクができなくなるため） --}}
                            <li role="presentation"><a href="{{ url("$page->permanent_link") }}">{{$page->page_name}}</a></li>
                            <li role="separator" class="divider"></li>

                            {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                            @include('plugins.user.menus.dropdown.menu_children',['children' => $page->children[0]])
                        </ul>
                    </li>
                @else
                    <li role="presentation" class="active">
                        <a href="{{ url("$page->permanent_link") }}" style="background-color: #3097d1; color: #ffffff;">
                            {{$page->page_name}}
                        </a>
                    </li>
                @endif
            @else
                <li role="presentation">
                    <a href="{{ url("$page->permanent_link") }}">
                        {{$page->page_name}}
                            @if (count($page->children) > 0)
                            <span class="caret"></span>
                        @endif
                    </a>
                </li>
            @endif
        @endif
    @endif
@endforeach
</ul>

@endif
