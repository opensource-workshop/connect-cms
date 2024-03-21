{{--
 * サイト管理（サイト設計書）のレイアウト情報設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>レイアウト設定（ブラウザ幅の100％で表示する設定）</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>ヘッダーエリア</td>
        <td>{{Configs::getConfigsValue($configs, 'browser_width_header', null)}}</td>
    </tr>
    <tr nobr="true">
        <td>センターエリア（左、メイン、右）</td>
        <td>{{Configs::getConfigsValue($configs, 'browser_width_center', null)}}</td>
    </tr>
    <tr nobr="true">
        <td>フッター</td>
        <td>{{Configs::getConfigsValue($configs, 'browser_width_footer', null)}}</td>
    </tr>
</table>
