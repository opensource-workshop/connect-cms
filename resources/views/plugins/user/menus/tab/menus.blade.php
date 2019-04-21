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
    @if ($page->id == $page_id)
        <li role="presentation" class="active"><a href="{{ url("$page->permanent_link") }}" style="background-color: #3097d1; color: #ffffff;">{{$page->page_name}}</a></li>
    @else
        <li role="presentation"><a href="{{ url("$page->permanent_link") }}">{{$page->page_name}}</a></li>
    @endif
@endforeach
</ul>

@endif
