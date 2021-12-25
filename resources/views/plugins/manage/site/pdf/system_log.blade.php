{{--
 * サイト管理（サイト設計書）のシステム管理 - エラーログ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h4>エラーログ設定</h4>

<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>ログファイルの形式</td>
        @if ($configs->firstWhere('name', 'log_handler')->value == '1') <td>日付毎のファイル</td> @else <td>単一ファイル</td> @endif
    </tr>
    <tr nobr="true">
        <td>ログファイル名の指定の有無</td>
        @if ($configs->firstWhere('name', 'log_filename_choice')->value == '1') <td>指定する</td> @else <td>指定しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>ログファイル名</td>
        <td>{{$configs->firstWhere('name', 'log_filename')->value}}</td>
    </tr>
</table>