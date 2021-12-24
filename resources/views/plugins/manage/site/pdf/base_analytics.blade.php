{{--
 * サイト管理（サイト設計書）のアクセス解析設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>アクセス解析</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr>
        <td>トラッキングコード</td>
        <td>{!!nl2br($configs->firstWhere('name', 'tracking_code')->value)!!}</td>
    </tr>
</table>