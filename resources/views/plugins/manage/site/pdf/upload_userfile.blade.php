{{--
 * サイト管理（サイト設計書）のアップロードファイル - ユーザディレクトリ一覧のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">アップロードファイル設定</h2>

<br />
<h4>ユーザディレクトリ一覧</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">ディレクトリ名</th>
        <th class="doc_th">閲覧設定</th>
    </tr>
    @foreach($configs->where('category', 'userdir_allow') as $config)
    <tr nobr="true">
        <td>{{$config->name}}</td>
        @if ($config->value == "allow_login") <td>ログインユーザのみ閲覧許可</td> @elseif ($config->value == "allow_all") <td>誰でも閲覧許可</td> @else <td>閲覧させない。</td> @endif
    </tr>
    @endforeach
    @if($configs->where('category', 'userdir_allow')->isEmpty())
    <tr nobr="true">
        <td colspan="2">ユーザディレクトリの設定はありません。</td>
    </tr>
    @endif
</table>