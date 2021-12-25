{{--
 * サイト管理（サイト設計書）のグループ設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">グループ設定</h2>

<br />
<h4>グループ一覧</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th" style="width: 50%;">グループ名</th>
        <th class="doc_th" style="width: 50%;">参加ユーザ数</th>
    </tr>
    @foreach($groups as $group)
    <tr nobr="true">
        <td>{{$group->name}}</td>
        <td>{{$group->group_users_count}}</td>
    </tr>
    @endforeach
    @if($groups->isEmpty())
    <tr nobr="true">
        <td colspan="2">グループの設定はありません。</td>
    </tr>
    @endif
</table>