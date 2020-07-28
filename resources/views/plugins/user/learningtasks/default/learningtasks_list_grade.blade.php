{{--
 * 課題管理成績一覧画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

<table class="table table-bordered">
<thead>
    <tr>
        <th>受講生</th>
        <th>レポート提出最終日時</th>
        <th>レポート評価</th>
        <th>試験提出最終日時</th>
        <th>試験評価</th>
    </tr>
</thead>
<tbody>
	@foreach ($statuses as $user)
    <tr>
        @empty($user[1])
            <td>－</td>
        @else
            <td>{{$user[1]->name}}</td>
        @endempty
        @empty($user[1])
            <td>－</td>
        @else
            <td>{{$user[1]->created_at}}</td>
        @endempty
        @empty($user[2])
            <td>－</td>
        @else
            <td>{{$user[2]->grade}}</td>
        @endempty
        @empty($user[5])
            <td>－</td>
        @else
            <td>{{$user[5]->created_at}}</td>
        @endempty
        @empty($user[6])
            <td>－</td>
        @else
            <td>{{$user[6]->grade}}</td>
        @endempty
    </tr>
	@endforeach
</tbody>
</table>

@endsection
