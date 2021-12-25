{{--
 * サイト管理（サイト設計書）のプラグイン設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">プラグイン設定</h2>

<br />
<h4>プラグイン一覧</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 10%;">表示順</th>
        <th class="doc_th" style="width: 15%;">表示</th>
        <th class="doc_th" style="width: 75%;">プラグイン名</th>
    </tr>
    @foreach($plugins as $plugin)
    <tr nobr="true">
        <td>{{$plugin->display_sequence}}</td>
        @if ($plugin->display_flag == 1) <td>表示する</td> @else <td>表示しない</td> @endif
        <td>{{$plugin->plugin_name}}</td>
    </tr>
    @endforeach
</table>