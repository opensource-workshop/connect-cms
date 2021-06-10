{{--
 * ログイン履歴画面テンプレート
--}}

{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        <div class="form-group table-responsive">
            <table class="table table-hover cc-font-90">
            <thead>
                <tr>
                    <th nowrap>ログインID</th>
                    <th nowrap>ログイン日時</th>
                    <th nowrap>IPアドレス</th>
                    <th nowrap>ユーザエージェント</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users_login_histories as $users_login_history)
                <tr>
                    <td>{{$users_login_history->userid}}</td>
                    <td>{{$users_login_history->logged_in_at->format('Y/m/d H:i')}}</td>
                    <td>{{$users_login_history->ip_address}}</td>
                    <td>{{$users_login_history->user_agent}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        <div class="text-center">
            {{ $users_login_histories->links() }}
        </div>

    </div>
</div>

@endsection
