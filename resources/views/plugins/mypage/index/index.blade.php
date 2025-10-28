{{--
 * マイページ画面のトップのメインテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category マイページ
--}}
{{-- マイページ画面ベース画面 --}}
@extends('plugins.mypage.mypage')

{{-- マイページ画面メイン部分のコンテンツ section:mypage_content で作ること --}}
@section('mypage_content')

@if(Configs::getConfigsValueWithHtmlRepair($configs, 'mypage_top_notice'))
<div class="card mb-3">
    <div class="card-body">
        {!! Configs::getConfigsValueWithHtmlRepair($configs, 'mypage_top_notice') !!}
    </div>
</div>
@endif

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        <div class="frame-setting-menu">
            <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
                <span class="d-md-none" style="margin: 0.5rem 0;">プロフィール</span>
                <div class="navbar-collapse collapse" id="collapsingNavbarLg">
                    <ul class="navbar-nav">
                        <li role="presentation" class="nav-item">
                        @if ($function == "index")
                            <span class="nav-link"><span class="active">プロフィール</span></span>
                        @else
                            <a href="{{url('/mypage')}}" class="nav-link">プロフィール</a></li>
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
                                @includeFirst(['plugins_option.mypage.index.include_index_column_value', 'plugins.mypage.index.include_index_column_value'])
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(Configs::getConfigsValueWithHtmlRepair($configs, 'mypage_bottom_notice'))
<div class="card mt-3">
    <div class="card-body">
        {!! Configs::getConfigsValueWithHtmlRepair($configs, 'mypage_bottom_notice') !!}
    </div>
</div>
@endif

@endsection
