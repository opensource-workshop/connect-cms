{{--
 * サイト管理（サイト設計書）のエラー設定のテンプレート
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
<h4>エラー設定</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr>
        <td>IPアドレス制限など権限がない場合の表示ページ</td>
        <td>{{$configs->firstWhere('name', 'page_permanent_link_403')->value}}</td>
    </tr>
    <tr>
        <td>指定ページがない場合の表示ページ</td>
        <td>{{$configs->firstWhere('name', 'page_permanent_link_404')->value}}</td>
    </tr>
</table>