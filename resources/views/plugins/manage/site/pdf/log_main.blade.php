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
        @if (Configs::getConfigsValue($configs, 'app_log_scope', null) == 'all') <td>全て</td>
        @elseif (Configs::getConfigsValue($configs, 'app_log_scope', null) == 'select') <td>選択したもののみ</td>
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
        @if (Configs::getConfigsValue($configs, 'save_log_type_login', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ログイン後のページ操作</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_authed', null) == '1') <td>〇</td> @else <td></td> @endif
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
        @if (Configs::getConfigsValue($configs, 'save_log_type_page', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>管理画面</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_manage', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>マイページ</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_mypage', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    {{-- <tr nobr="true">
        <td>メール配信設定</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_unsubscribe', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr> --}}
    <tr nobr="true">
        <td>API</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_api', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>検索キーワード</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_search_keyword', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>メール送信</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_sendmail', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>パスワードページ認証</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_passwordpage', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ダウンロード</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_download', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>CSS</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_css', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ファイル</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_file', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>パスワード関係</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_password', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>ユーザ登録</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_register', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>コア側処理</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_core', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>言語切り替え</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_language', null) == '1') <td>〇</td> @else <td></td> @endif
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
        @if (Configs::getConfigsValue($configs, 'save_log_type_http_get', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
    <tr nobr="true">
        <td>POST</td>
        @if (Configs::getConfigsValue($configs, 'save_log_type_http_post', null) == '1') <td>〇</td> @else <td></td> @endif
    </tr>
</table>
