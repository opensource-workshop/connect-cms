{{--
 * サイト管理（サイト設計書）のファビコン設定のテンプレート
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
<h4>ファビコン情報</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr>
        <td>ファビコン・ファイル</td>
        <td>{{$configs->firstWhere('name', 'favicon')->value}}</td>
    </tr>
    <tr>
        <td>ファビコン画像</td>
        @if ($configs->firstWhere('name', 'favicon')->value)
            <td><img src="{{url('/')}}/uploads/favicon/{{$configs->firstWhere('name', 'favicon')->value}}" style="width: 50px;" width=50></td>
        @else
            <td><br /></td>
        @endif
    </tr>
</table>
