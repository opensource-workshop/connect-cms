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
                        {{-- <tr>
                            <th style="width:20%;" nowrap="nowrap">ユーザID</th>
                            <td nowrap="nowrap">{{ $user->id }}</td>
                        </tr> --}}
                        @foreach($users_columns as $column)
                            @if ($column->is_show_my_page == ShowType::not_show)
                                @continue
                            @endif

                            @if ($column->column_type == UserColumnType::user_name)
                                {{-- ユーザ名 --}}
                                <tr>
                                    <th style="width:20%;" nowrap="nowrap">{{$column->column_name}}</th>
                                    <td nowrap="nowrap">{{ $user->name }}</td>
                                </tr>
                            @elseif ($column->column_type == UserColumnType::login_id)
                                {{-- ログインID --}}
                                <tr>
                                    <th style="width:20%;" nowrap="nowrap">{{$column->column_name}}</th>
                                    <td nowrap="nowrap">{{ $user->userid }}</td>
                                </tr>
                            @elseif ($column->column_type == UserColumnType::user_email)
                                {{-- メールアドレス --}}
                                <tr>
                                    <th style="width:20%;" nowrap="nowrap">{{$column->column_name}}</th>
                                    <td nowrap="nowrap">{{ $user->email }}</td>
                                </tr>
                            @elseif ($column->column_type == UserColumnType::user_password)
                                {{-- 表示しない --}}
                            @elseif ($column->column_type == UserColumnType::created_at)
                                {{-- 登録日時 --}}
                                <tr>
                                    <th style="width:20%;" nowrap="nowrap">{{$column->column_name}}</th>
                                    <td nowrap="nowrap">{{ $user->created_at->format('Y/m/d H:i') }}</td>
                                </tr>
                            @elseif ($column->column_type == UserColumnType::updated_at)
                                {{-- 更新日時 --}}
                                <tr>
                                    <th style="width:20%;" nowrap="nowrap">{{$column->column_name}}</th>
                                    <td nowrap="nowrap">{{ $user->updated_at->format('Y/m/d H:i') }}</td>
                                </tr>
                            @else
                                @php
                                    $input_col = $input_cols->firstWhere('users_columns_id', $column->id);
                                @endphp
                                <tr class="input-cols">
                                    <th>{{$input_col->column_name}}</th>
                                    <td>{{$input_col->value}}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
