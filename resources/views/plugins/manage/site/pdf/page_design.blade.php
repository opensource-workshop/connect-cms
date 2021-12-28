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
<h4>ページ設定（デザイン情報）</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 8%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 19%;">背景色</th>
        <th class="doc_th" style="width: 19%;">ヘッダー色</th>
        <th class="doc_th" style="width: 19%;">クラス名</th>
    </tr>
    @foreach($pages as $page)
    <tr nobr="true">
        <td>{{$page->id}}</td>
        <td>{{$page->page_name}}</td>
        <td>{{$page->background_color}}</td>
        <td>{{$page->header_color}}</td>
        <td>{{$page->class}}</td>
    </tr>
    @endforeach
</table>
