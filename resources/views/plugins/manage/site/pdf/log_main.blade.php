{{--
 * サイト管理（サイト設計書）のログ管理 - ログ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">ログ管理</h2>

<br />
<h4>ログ設定</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>記録範囲</td>
        @if ($configs->firstWhere('name', 'app_log_scope')->value == 'all') <td>全て</td>
        @elseif ($configs->firstWhere('name', 'app_log_scope')->value == 'select') <td>選択したもののみ</td>
        @else <td></td>
        @endif
    </tr>
</table>

<h4>記録するログ種別 - ログイン関係</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>ログイン・ログアウト</td>
        @if ($configs->firstWhere('name', 'save_log_type_login')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ログイン後のページ操作</td>
        @if ($configs->firstWhere('name', 'save_log_type_authed')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
</table>

<h4>記録するログ種別 - 種別</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>一般ページ</td>
        @if ($configs->firstWhere('name', 'save_log_type_page')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>管理画面</td>
        @if ($configs->firstWhere('name', 'save_log_type_manage')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>マイページ</td>
        @if ($configs->firstWhere('name', 'save_log_type_mypage')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>API</td>
        @if ($configs->firstWhere('name', 'save_log_type_api')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>検索キーワード</td>
        @if ($configs->firstWhere('name', 'save_log_type_search_keyword')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>メール送信</td>
        @if ($configs->firstWhere('name', 'save_log_type_sendmail')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>パスワードページ認証</td>
        @if ($configs->firstWhere('name', 'save_log_type_passwordpage')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ダウンロード</td>
        @if ($configs->firstWhere('name', 'save_log_type_download')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>CSS</td>
        @if ($configs->firstWhere('name', 'save_log_type_css')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ファイル</td>
        @if ($configs->firstWhere('name', 'save_log_type_file')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>パスワード関係</td>
        @if ($configs->firstWhere('name', 'save_log_type_password')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ユーザ登録</td>
        @if ($configs->firstWhere('name', 'save_log_type_register')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>コア側処理</td>
        @if ($configs->firstWhere('name', 'save_log_type_core')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>言語切り替え</td>
        @if ($configs->firstWhere('name', 'save_log_type_language')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
</table>

<h4>記録するログ種別 - HTTPメソッド</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>GET</td>
        @if ($configs->firstWhere('name', 'save_log_type_http_get')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>POST</td>
        @if ($configs->firstWhere('name', 'save_log_type_http_post')->value == '1') <td>〇</td> @else <td></td> @endif
    </tr>
</table>