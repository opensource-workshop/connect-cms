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
<h4>ページ設定（動作関連）</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 8%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 10%;">新ウィンドウ</th>
        <th class="doc_th" style="width: 12%;">下層ページ転送</th>
        <th class="doc_th" style="width: 35%;">外部リンク</th>
    </tr>
    @foreach($pages as $page)
    <tr nobr="true">
        <td>{{$page->id}}</td>
        <td>{{$page->page_name}}</td>
        <td>{{$page->othersite_url_target}}</td>
        @if ($page->transfer_lower_page_flag == 1) <td>〇</td> @else <td></td> @endif
        <td>{{$page->othersite_url}}</td>
    </tr>
    @endforeach
</table>