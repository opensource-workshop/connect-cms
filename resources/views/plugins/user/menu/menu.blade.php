{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@if ($pages)
<div class="list-group">
@foreach($pages as $page)

    {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
    <a href="{{ url("$page->permanent_link") }}" class="list-group-item">

    {{-- 各ページの深さをもとにインデントの表現 --}}
    @for ($i = 0; $i < $page->depth; $i++)
        <span @if ($i+1==$page->depth) class="glyphicon glyphicon-chevron-right" style="color: #c0c0c0;"@else style="padding-left:15px;"@endif></span>
    @endfor
    {{$page->page_name}}
    </a>

@endforeach
</div>
@endif
