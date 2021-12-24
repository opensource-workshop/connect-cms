{{--
 * サイト管理（サイト設計書）のエラー設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>エラー設定</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr>
        <td>IPアドレス制限など権限がない場合の表示ページ</td>
        <td>{{$configs->firstWhere('name', 'page_permanent_link_403')->value}}</td>
    </tr>
    <tr>
        <td>指定ページがない場合の表示ページ</td>
        <td>{{$configs->firstWhere('name', 'page_permanent_link_404')->value}}</td>
    </tr>
</table>