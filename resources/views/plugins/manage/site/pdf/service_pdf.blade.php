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
        <td>{{$configs->firstWhere('name', 'width_of_pdf_thumbnails_initial')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>初期に選択させるサムネイルの数</td>
        <td>{{$configs->firstWhere('name', 'number_of_pdf_thumbnails_initial')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>サムネイルのリンク</td>
        @if ($configs->firstWhere('name', 'link_of_pdf_thumbnails')->value == 'pdf') <td>PDFを開く</td>
        @elseif ($configs->firstWhere('name', 'link_of_pdf_thumbnails')->value == 'image') <td>画像を開く</td>
        @else <td>使用しない</td> @endif
    </tr>
</table>