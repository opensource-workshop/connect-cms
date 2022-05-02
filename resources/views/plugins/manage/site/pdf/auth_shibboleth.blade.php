{{--
 * サイト管理（サイト設計書）の外部認証 - Shibboleth認証のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>Shibboleth認証</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>login_path</td>
        <td>{{config('cc_shibboleth_config.login_path')}}</td>
    </tr>
    <tr nobr="true">
        <td>userid</td>
        <td>{{config('cc_shibboleth_config.userid')}}</td>
    </tr>
    <tr nobr="true">
        <td>user_name</td>
        <td>{{config('cc_shibboleth_config.user_name')}}</td>
    </tr>
    <tr nobr="true">
        <td>user_email</td>
        <td>{{config('cc_shibboleth_config.user_email')}}</td>
    </tr>
</table>