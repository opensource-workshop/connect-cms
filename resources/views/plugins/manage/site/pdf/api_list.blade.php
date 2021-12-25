{{--
 * サイト管理（サイト設計書）のAPI設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">API管理</h2>

<br />
<h4>Secret Code 一覧</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 25%;">利用名</th>
        <th class="doc_th" style="width: 25%;">秘密コード</th>
        <th class="doc_th" style="width: 25%;">制限IPアドレス</th>
        <th class="doc_th" style="width: 25%;">使用API</th>
    </tr>
    @foreach($api_secrets as $api_secret)
    <tr nobr="true">
        <td>{{$api_secret->secret_name}}</td>
        <td>{{$api_secret->secret_code}}</td>
        <td>{{$api_secret->ip_address}}</td>
        <td>{!!nl2br($api_secret->apis)!!}</td>
    </tr>
    @endforeach
    @if($api_secrets->isEmpty())
    <tr nobr="true">
        <td colspan="4">APIの設定はありません。</td>
    </tr>
    @endif
</table>