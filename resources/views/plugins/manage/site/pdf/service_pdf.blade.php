{{--
 * サイト管理（サイト設計書）の外部サービス設定 - PDFアップロードのテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>PDFアップロード</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>初期に選択させるサムネイルの大きさ</td>
        <td>{{Configs::getConfigsValue($configs, 'width_of_pdf_thumbnails_initial', null)}}</td>
    </tr>
    <tr nobr="true">
        <td>初期に選択させるサムネイルの数</td>
        <td>{{Configs::getConfigsValue($configs, 'number_of_pdf_thumbnails_initial', null)}}</td>
    </tr>
    <tr nobr="true">
        <td>サムネイルのリンク</td>
        @if (Configs::getConfigsValue($configs, 'link_of_pdf_thumbnails', null) == 'pdf') <td>PDFを開く</td>
        @elseif (Configs::getConfigsValue($configs, 'link_of_pdf_thumbnails', null) == 'image') <td>画像を開く</td>
        @else <td>使用しない</td> @endif
    </tr>
</table>
