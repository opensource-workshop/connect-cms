{{--
 * ユーザ一覧のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- 機能選択タブ --}}
@include('plugins.manage.user.user_manage_tab')

<div class="card">
<div class="card-body">

    <div class="form-group">
        <table class="table table-hover cc-font-90">
        <thead>
            <tr>
                <th nowrap>ユーザID</th>
                <th nowrap>ユーザー名</th>
                <th nowrap>eメール</th>
                <th nowrap>権限</th>
                <th nowrap>作成日</th>
                <th nowrap>更新日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>
                    <a href="{{url('/')}}/manage/user/edit/{{$user->id}}">
                        <i class="far fa-edit"></i>
                    </a>
                    {{$user->userid}}
                </td>
                <td>{{$user->name}}</td>
                <td>{{$user->email}}</td>
                @if ($user->role == 1)
                    <td>システム管理者</td>
                @elseif ($user->role == 2)
                    <td>サイト管理者</td>
                @elseif ($user->role == 3)
                    <td>ユーザ管理者</td>
                @elseif ($user->role == 10)
                    <td>運用管理者</td>
                @elseif ($user->role == 11)
                    <td>承認者</td>
                @elseif ($user->role == 12)
                    <td>編集者</td>
                @elseif ($user->role == 0)
                    <td>ゲスト</td>
                @else
                    <td>{{$user->role}}</td>
                @endif
                <td>{{$user->created_at}}</td>
                <td>{{$user->updated_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

</div>
</div>

@endsection
