{{--
 * サイト管理（サイト設計書）の祝日管理 - 変更内容一覧のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">祝日管理</h2>

<br />
<h4>変更内容一覧</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 25%;">日付</th>
        <th class="doc_th" style="width: 50%;">祝日名</th>
        <th class="doc_th" style="width: 25%;">ステータス</th>
    </tr>
    @foreach($holidays as $holiday)
    <tr nobr="true">
        <td>{{$holiday->holiday_date}}</td>
        <td>{{$holiday->holiday_name}}</td>
        @if ($holiday->status == 0) <td>有効</td> @else <td>無効</td> @endif
    </tr>
    @endforeach
    @if($holidays->isEmpty())
    <tr nobr="true">
        <td colspan="3">追加及び無効にされた祝日はありません。</td>
    </tr>
    @endif
</table>