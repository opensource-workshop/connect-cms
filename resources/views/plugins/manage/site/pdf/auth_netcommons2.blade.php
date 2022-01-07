{{--
 * サイト管理（サイト設計書）の外部認証 - NetCommons2認証のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>NetCommons2認証</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>サイトURL</td>
        <td>{{$configs->firstWhere('name', 'auth_netcomons2_site_url')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>site_key</td>
        <td>{{$configs->firstWhere('name', 'auth_netcomons2_site_key')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>Salt</td>
        <td>{{$configs->firstWhere('name', 'auth_netcomons2_salt')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>デフォルトで追加するオリジナル権限</td>
        <td>{{$configs->firstWhere('name', 'auth_netcomons2_add_role')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>管理者操作用パスワード</td>
        <td>{{$configs->firstWhere('name', 'auth_netcomons2_admin_password')->value}}</td>
    </tr>
</table>