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
<h4>ページ設定（制限関連）</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 8%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 14%;">閲覧パスワード</th>
        <th class="doc_th" style="width: 14%;">メンバーシップ等</th>
        <th class="doc_th" style="width: 14%;">ページ権限</th>
        <th class="doc_th" style="width: 15%;">IP制限</th>
    </tr>
    @foreach($pages as $page)
    <tr nobr="true">
        <td>{{$page->id}}</td>
        <td>{{$page->page_name}}</td>
        <td>{{$page->password}}</td>
        @if ($page->membership_flag == 1) <td>メンバーシップページ</td>
        @elseif ($page->membership_flag == 2) <td>ログインユーザ全員参加</td>
        @else <td></td>
        @endif
        @if ($page->page_roles->isNotEmpty()) <td>権限あり</td>
        @else <td></td>
        @endif
        <td>{{$page->ip_address}}</td>
    </tr>
    @endforeach
</table>
