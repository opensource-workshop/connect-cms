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
<h2 style="text-align: center; font-size: 32px;">グループ設定</h2>

<br />
<h4>グループ一覧</h4>
<table border="0" class="table_css">
    <tr>
        <th class="doc_th" style="width: 50%;">グループ名</th>
        <th class="doc_th" style="width: 50%;">参加ユーザ数</th>
    </tr>
    @foreach($groups as $group)
    <tr>
        <td>{{$group->name}}</td>
        <td>{{$group->group_users_count}}</td>
    </tr>
    @endforeach
</table>