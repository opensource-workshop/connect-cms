{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($pages)

    @php
        // アクティブ・マークを付けるページID の算出
        // アクティブ・マークを付けるページとは、以下の条件を満たすもの
        // 選択されているページ＆表示中のページ
        // 選択されているページが非表示の場合、表示されている中での、最後の上位階層のページ

        // プログラムでの、値の見つけ方
        // PHP のグローバル変数を宣言し、アクティブ・マークのページIDを保持する。
        // 再帰関数でページを順に見ていき、条件に合致するページIDでグローバル変数を上書きながら進む
        // 残ったページIDがアクティブ・マークのページになる
        global $active_page_id;
        $active_page_id = 0;

        // アクティブ・マークを付けるページID を探す再帰関数
        function factorial($page, $ancestors, $page_roles) {
            global $active_page_id;

            if ($page->isView(Auth::user(), false, true, $page_roles)) {
                if ($ancestors->contains('id', $page->id)) {
                    $active_page_id = $page->id;
                }
            }

            if (count($page->children) > 0) {
                foreach ($page->children as $children) {
                    factorial($children, $ancestors, $page_roles);
                }
            }
            return;
        }
        foreach ($pages as $page) {
            factorial($page, $ancestors, $page_roles);
        }
    @endphp

    <nav aria-label="タブメニュー">
    <ul class="nav nav-tabs nav-justified d-none d-md-flex">
    @foreach($pages as $page)

        {{-- 自階層で非表示のページは対象外 --}}
        @if ($page->isView(Auth::user(), false, true, $page_roles))
            @if ($active_page_id == $page->id)
                <li role="presentation" class="nav-item {{'depth-' . $page->depth}} {{$page->getClass()}}"><a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="nav-link active">{{$page->page_name}}</a></li>
            @else
                <li role="presentation" class="nav-item {{'depth-' . $page->depth}} {{$page->getClass()}}"><a href="{{$page->getUrl()}}" {!!$page->getUrlTargetTag()!!} class="nav-link">{{$page->page_name}}</a></li>
            @endif

            @php
                $view_pages[] = $page->id;
            @endphp

        @endif

        {{-- 子供のページがある場合 --}}
        @if (count($page->children) > 0)
            {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
            @foreach($page->children as $children)
                @include('plugins.user.menus.tab_flat.menu_children',['children' => $children, 'page_id' => $page_id, 'active_page_id' => $active_page_id])
            @endforeach

        @endif
    @endforeach
    </ul>
    </nav>
@endif
@endsection
