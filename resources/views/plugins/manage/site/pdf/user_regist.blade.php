{{--
 * サイト管理（サイト設計書）のサイト基本設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">ユーザ設定</h2>

<br />
<h4>自動ユーザ登録設定</h4>

【自動ユーザ登録の使用】<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>自動ユーザ登録の使用</td>
        @if ($configs->firstWhere('name', 'user_register_enable')->value == '1') <td>許可する</td> @else <td>許可しない</td> @endif
    </tr>
</table>

<br /><br />
【メール送信先】<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>メール送信先</td>
        @if ($configs->firstWhere('name', 'user_register_mail_send_flag')->value == '1') <td>使用する</td> @else <td>使用しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>送信するメールアドレス</td>
        <td>{{$configs->firstWhere('name', 'user_register_mail_send_address')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>登録者にメール送信する</td>
        @if ($configs->firstWhere('name', 'user_register_user_mail_send_flag')->value == '1') <td>送信する</td> @else <td>送信しない</td> @endif
    </tr>
</table>

<br /><br />
【仮登録メール】<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>登録者に仮登録メールを送信する</td>
        @if ($configs->firstWhere('name', 'user_register_temporary_regist_mail_flag')->value == '1') <td>送信する</td> @else <td>送信しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>仮登録メール件名</td>
        <td>{{$configs->firstWhere('name', 'user_register_temporary_regist_mail_subject')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>仮登録メールフォーマット</td>
        <td>{!!nl2br($configs->firstWhere('name', 'user_register_temporary_regist_mail_format')->value)!!}</td>
    </tr>
    <tr nobr="true">
        <td>仮登録後のメッセージ</td>
        <td>{{$configs->firstWhere('name', 'user_register_temporary_regist_after_message')->value}}</td>
    </tr>
</table>

<br /><br />
【本登録メール】<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>本登録メール件名</td>
        <td>{{$configs->firstWhere('name', 'user_register_mail_subject')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>本登録メールフォーマット</td>
        <td>{!!nl2br($configs->firstWhere('name', 'user_register_mail_format')->value)!!}</td>
    </tr>
    <tr nobr="true">
        <td>本登録後のメッセージ</td>
        <td>{{$configs->firstWhere('name', 'user_register_after_message')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>ヘッダーバーの表示</td>
        @if ($configs->firstWhere('name', 'base_header_hidden')->value == '1') <td>表示しない</td> @else <td>表示する</td> @endif
    </tr>
</table>

<br /><br />
【個人情報保護方針】<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>個人情報保護方針への同意</td>
        @if ($configs->firstWhere('name', 'user_register_requre_privacy')->value == '1') <td>同意を求める</td> @else <td>同意を求めない</td> @endif
    </tr>
    <tr nobr="true">
        <td>個人情報保護方針の表示内容</td>
        <td>次行を参照</td>
    </tr>
    <tr nobr="true">
        <td colspan="2">{!!nl2br($configs->firstWhere('name', 'user_register_privacy_description')->value)!!}</td>
    </tr>
</table>

<br /><br />
【その他】<br />
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>ユーザ登録について</td>
        <td>次行を参照</td>
    </tr>
    <tr nobr="true">
        <td colspan="2">{!!nl2br($configs->firstWhere('name', 'user_register_description')->value)!!}</td>
    </tr>
</table>