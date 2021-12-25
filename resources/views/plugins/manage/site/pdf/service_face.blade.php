{{--
 * サイト管理（サイト設計書）の外部サービス設定 - AI顔認識のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>AI顔認識</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>初期に選択させる画像の大きさ</td>
        <td>{{$configs->firstWhere('name', 'face_ai_initial_size')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>初期に選択させるモザイクの粗さ</td>
        <td>{{$configs->firstWhere('name', 'face_ai_initial_fineness')->value}}</td>
    </tr>
</table>