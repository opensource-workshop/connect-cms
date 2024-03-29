{{--
 * サイト管理（サイト設計書）のWYSIWYG設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>WYSIWYG設定</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>文字サイズの使用</td>
        @if (Configs::getConfigsValue($configs, 'fontsizeselect', null) == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>初期に選択させる画像サイズ</td>
        @php
            $resized_image_size_initial = Configs::getConfigsValue($configs, "resized_image_size_initial", ResizedImageSize::getDefault());
        @endphp
        <td>{{ ResizedImageSize::getDescription($resized_image_size_initial) }}</td>
    </tr>
</table>
