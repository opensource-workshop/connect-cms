{{--
 * サイト管理（サイト設計書）のページ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
<style type="text/css">
/* テーブル */
.table_css {
    border-collapse:  collapse;     /* セルの線を重ねる */
}
.table_css th, .table_css td {
    border: 0.1px solid #000;       /* 枠線指定 */
}
.doc_th {
    background-color: #d0d0d0;      /* 背景色指定 */
}
</style>

<br />
<h2 style="text-align: center; font-size: 32px;">ページ設定</h2>

<br />
<h4>ページ設定（基本情報）</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th" style="width: 5%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 40%;">固定リンク</th>
        <th class="doc_th" style="width: 10%;">メニュー<br />表示</th>
        <th class="doc_th" style="width: 10%;">レイ<br />アウト</th>
    </tr>
    @foreach($pages as $page)
    <tr>
        <td>{{$page->id}}</td>
        <td>{{$page->page_name}}</td>
        <td>{{$page->permanent_link}}</td>
        @if ($page->base_display_flag == 1) <td>〇</td> @else <td></td> @endif
        <td><img src="{{url('/')}}/images/core/layout/1101.png" style="width: 10px;"></td>
    </tr>
    @endforeach
</table>

<h4>ページ設定（制限関連）</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th" style="width: 5%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 15%;">閲覧パスワード</th>
        <th class="doc_th" style="width: 15%;">メンバーシップ等</th>
        <th class="doc_th" style="width: 15%;">ページ権限</th>
        <th class="doc_th" style="width: 15%;">IP制限</th>
    </tr>
    @foreach($pages as $page)
    <tr>
        <td>{{$page->id}}</td>
        <td>{{$page->page_name}}</td>
        <td>{{$page->password}}</td>
        <td>？？？？</td>
        <td>？？？？</td>
        <td>{{$page->ip_address}}</td>
    </tr>
    @endforeach
</table>

<h4>ページ設定（デザイン情報）</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th" style="width: 5%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 20%;">背景色</th>
        <th class="doc_th" style="width: 20%;">ヘッダー色</th>
        <th class="doc_th" style="width: 20%;">クラス名</th>
    </tr>
    @foreach($pages as $page)
    <tr>
        <td>{{$page->id}}</td>
        <td>{{$page->page_name}}</td>
        <td>{{$page->background_color}}</td>
        <td>{{$page->header_color}}</td>
        <td>{{$page->class}}</td>
    </tr>
    @endforeach
</table>

<h4>ページ設定（動作関連）</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th" style="width: 5%;">ID</th>
        <th class="doc_th" style="width: 35%;">ページ名</th>
        <th class="doc_th" style="width: 20%;">新ウィンドウ</th>
        <th class="doc_th" style="width: 40%;">外部リンク</th>
    </tr>
    @foreach($pages as $page)
    <tr>
        <td>{{$page->id}}</td>
        <td>{{$page->page_name}}</td>
        <td>{{$page->othersite_url_target}}</td>
        <td>{{$page->othersite_url}}</td>
    </tr>
    @endforeach
</table>