{{--
 * サイト管理（サイト設計書）のシステム管理 - サーバ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">システム管理</h2>

<br />
<h4>サーバ設定</h4>

<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>画像リサイズ時のPHPメモリ数</td>
        <td>{{$configs->firstWhere('name', 'memory_limit_for_image_resize')->value}}</td>
    </tr>
</table>