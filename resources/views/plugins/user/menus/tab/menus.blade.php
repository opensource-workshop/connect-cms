{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@if ($pages)

<ul class="nav nav-tabs nav-justified d-none d-md-flex" style="">
@foreach($pages as $page)

    {{-- 非表示のページは対象外 --}}
    @if ($page->display_flag == 1)
        @if ($page->id == $page_id)
            <li role="presentation" class="nav-item"><a href="{{ url("$page->permanent_link") }}" class="nav-link active">{{$page->page_name}}</a></li>
        @else
            <li role="presentation" class="nav-item"><a href="{{ url("$page->permanent_link") }}" class="nav-link">{{$page->page_name}}</a></li>
        @endif
    @endif
@endforeach
</ul>

@endif
