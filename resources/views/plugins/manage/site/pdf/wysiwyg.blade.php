{{--
 * サイト管理（サイト設計書）のWYSIWYG設定のテンプレート
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
<h4>WYSIWYG設定</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr>
        <td>文字サイズの使用</td>
        @if ($configs->firstWhere('name', 'fontsizeselect')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
    <tr>
        <td>初期に選択させる画像サイズ</td>
        @if ($configs->firstWhere('name', 'resized_image_size_initial')->value == '1200') <td>大(1200px)</td>
        @elseif ($configs->firstWhere('name', 'resized_image_size_initial')->value == '800') <td>中(800px)</td>
        @elseif ($configs->firstWhere('name', 'resized_image_size_initial')->value == '400') <td>小(400px)</td>
        @elseif ($configs->firstWhere('name', 'resized_image_size_initial')->value == '200') <td>極小(200px)</td>
        @else <td>原寸(以下の幅、高さ)</td>
        @endif
    </tr>
</table>