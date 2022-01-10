{{--
 * サイト管理（サイト設計書）の外部サービス設定 - WYSIWYG設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">外部サービス設定</h2>

<br />
<h4>WYSIWYG設定</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>翻訳</td>
        @if ($configs->firstWhere('name', 'use_translate')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>PDFアップロード</td>
        @if ($configs->firstWhere('name', 'use_pdf_thumbnail')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>AI顔認識</td>
        @if ($configs->firstWhere('name', 'use_face_ai')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
</table>