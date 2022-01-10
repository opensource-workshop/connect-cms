{{--
 * サイト管理（サイト設計書）の外部認証 - 認証設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">外部認証</h2>

<br />
<h4>認証設定</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>外部認証</td>
        @if ($configs->firstWhere('name', 'use_auth_method')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>使用する外部認証</td>
        <td>{{$configs->firstWhere('name', 'auth_method_event')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>通常ログインも使用</td>
        @if ($configs->firstWhere('name', 'use_normal_login_along_with_auth_method')->value == '1') <td>使用しない</td> @else <td>使用する</td> @endif
    </tr>
</table>