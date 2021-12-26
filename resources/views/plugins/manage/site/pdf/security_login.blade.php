{{--
 * サイト管理（サイト設計書）のセキュリティ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">セキュリティ設定</h2>

<br />
<h4>ログイン制限</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 10%;">適用順</th>
        <th class="doc_th" style="width: 20%;">IPアドレス</th>
        <th class="doc_th" style="width: 20%;">権限</th>
        <th class="doc_th" style="width: 35%;">メモ</th>
        <th class="doc_th" style="width: 15%;">許可/拒否</th>
    </tr>
    @foreach($login_permits as $login_permit)
    <tr nobr="true">
        <td>{{$login_permit->apply_sequence}}</td>
        <td>{{$login_permit->ip_address}}</td>
        <td>{{$login_permit->getRoleName($login_permit)}}</td>
        <td>{{$login_permit->memo}}</td>
        @if ($login_permit->reject == 0) <td>許可する</td> @else <td>許可しない</td> @endif
    </tr>
    @endforeach
    @if($login_permits->isEmpty())
    <tr nobr="true">
        <td colspan="5">ログイン制限の設定はありません。</td>
    </tr>
    @endif
</table>
※ IPアドレス (*でALL)<br />
