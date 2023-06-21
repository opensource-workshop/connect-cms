{{--
 * サイト管理（サイト設計書）の外部認証 - LDAP認証のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>LDAP認証</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>LDAP URI</td>
        <td>{{Configs::getConfigsValue($configs, 'auth_ldap_uri', null)}}</td>
    </tr>
    <tr nobr="true">
        <td>DNタイプ</td>
        @if (Configs::getConfigsValue($configs, 'use_auth_method', null) == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>通常ログインも使用</td>
        @if (Configs::getConfigsValue($configs, 'auth_ldap_dn_type', null) == 'dn') <td>DN (uid=ユーザID,DN形式)</td> @elseif (Configs::getConfigsValue($configs, 'auth_ldap_dn_type', null) == 'active_directory') <td>Active Directory (ユーザID@DN形式)</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>DN</td>
        <td>{{Configs::getConfigsValue($configs, 'auth_ldap_dn', null)}}</td>
    </tr>
</table>
