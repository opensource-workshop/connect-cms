{{--
 * サイト管理（サイト設計書）のページ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">ページ設定</h2>

<br />
<h4>ページ設定（基本情報）</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 8%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 35%;">固定リンク</th>
        <th class="doc_th" style="width: 13%;">メニュー<br />表示</th>
        <th class="doc_th" style="width: 9%;">レイ<br />アウト</th>
    </tr>
    @foreach($pages as $page)
    <tr nobr="true">
        <td>{{$page->id}}</td>
        <td>{{str_repeat("・", $page->depth)}}{{$page->page_name}}</td>
        <td>{{$page->permanent_link}}</td>
        @if ($page->base_display_flag == 1) <td>〇</td> @else <td></td> @endif
        @if ($page->getSimpleLayout()) <td><img src="{{url('/')}}/images/core/layout/{{$page->getSimpleLayout()}}.png" style="width: 10px;"></td> @else <td></td> @endif
    </tr>
    @endforeach
</table>