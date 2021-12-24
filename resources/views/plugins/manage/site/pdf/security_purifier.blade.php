{{--
 * サイト管理（サイト設計書）のセキュリティ設定のHTML記述制限テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>HTML記述制限</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th">権限</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr>
        <td>コンテンツ管理者</td>
        @if ($purifiers['role_article_admin'] == 0) <td>制限しない</td> @else <td>制限する</td> @endif
    </tr>
    <tr>
        <td>プラグイン管理者</td>
        @if ($purifiers['role_arrangement'] == 0) <td>制限しない</td> @else <td>制限する</td> @endif
    </tr>
    <tr>
        <td>モデレータ</td>
        @if ($purifiers['role_article'] == 0) <td>制限しない</td> @else <td>制限する</td> @endif
    </tr>
    <tr>
        <td>承認者</td>
        @if ($purifiers['role_approval'] == 0) <td>制限しない</td> @else <td>制限する</td> @endif
    </tr>
    <tr>
        <td>編集者</td>
        @if ($purifiers['role_reporter'] == 0) <td>制限しない</td> @else <td>制限する</td> @endif
    </tr>
    <tr>
        <td>ゲスト</td>
        @if ($purifiers['role_guest'] == 0) <td>制限しない</td> @else <td>制限する</td> @endif
    </tr>
</table>