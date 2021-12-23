{{--
 * サイト管理（サイト設計書）の多言語設定のテンプレート
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
<h4>多言語設定</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr>
        <td>多言語設定の使用</td>
        @if ($configs->firstWhere('name', 'language_multi_on')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
</table>

@foreach ($configs->where('category', 'language')->sortBy('id') as $language)
    @if ($loop->first)
        <h4>設定済み言語</h4>
        <table border="0" class="table_css">
            <tr>
                <th class="doc_th">言語</th>
                <th class="doc_th">URL</th>
            </tr>
    @endif
        <tr>
            <td>{{$language->value}}</td>
            <td>{{$language->additional1}}</td>
        </tr>
    @if ($loop->last)
        </table>
    @endif
@endforeach
