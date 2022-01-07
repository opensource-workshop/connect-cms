{{--
 * サイト管理（サイト設計書）のメッセージ管理の初回確認メッセージのテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">メッセージ管理</h2>

<br />
<h4>初回確認メッセージ</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>表示の有無</td>
        @if ($configs->firstWhere('name', 'message_first_show_type')->value == '1') <td>表示する</td> @else <td>表示しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>ウィンドウ外クリックによる離脱</td>
        @if ($configs->firstWhere('name', 'message_first_permission_type')->value == '1') <td>許可する</td> @else <td>許可しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>メッセージ内容</td>
        <td>{!!$configs->firstWhere('name', 'message_first_content')->value!!}</td>
    </tr>
    <tr nobr="true">
        <td>ボタン名</td>
        <td>{{$configs->firstWhere('name', 'message_first_button_name')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>除外URL</td>
        <td>{{$configs->firstWhere('name', 'message_first_exclued_url')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>メッセージエリア任意クラス</td>
        <td>{{$configs->firstWhere('name', 'message_first_optional_class')->value}}</td>
    </tr>
</table>