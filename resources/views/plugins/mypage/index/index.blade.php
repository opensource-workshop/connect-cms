{{--
    マイページ画面のトップのメインテンプレート
--}}
{{-- マイページ画面ベース画面 --}}
@extends('plugins.mypage.mypage')

{{-- マイページ画面メイン部分のコンテンツ section:mypage_content で作ること --}}
@section('mypage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        <div class="frame-setting-menu">
            <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
                <span class="d-md-none" style="margin: 0.5rem 0;">マイページ</span>
                <div class="navbar-collapse collapse" id="collapsingNavbarLg">
                    <ul class="navbar-nav">
                        <li role="presentation" class="nav-item">
                        @if ($function == "index")
                            <span class="nav-link"><span class="active">マイページ</span></span>
                        @else
                            <a href="{{url('/mypage/profile')}}" class="nav-link">マイページ</a></li>
                        @endif
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <div class="user-area form-group">
                <table class="table table-hover cc-font-90">
                    <tbody>
                        <tr>
                            <th style="width:15%;" nowrap="nowrap">ユーザID</th>
                            <td nowrap="nowrap">{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th nowrap="nowrap">ログインID</th>
                            <td nowrap="nowrap">{{ $user->userid }}</td>
                        </tr>
                        <tr>
                            <th nowrap="nowrap">氏名</th>
                            <td nowrap="nowrap">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th nowrap="nowrap">メールアドレス</th>
                            <td nowrap="nowrap">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th nowrap="nowrap">登録日時</th>
                            <td nowrap="nowrap">{{ $user->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                        @foreach($user_input_cols as $user_input_col)
                            <tr class="input-cols">
                                <th>{{$user_input_col->column_name}}</th>
                                <td>{{$user_input_col->value}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
