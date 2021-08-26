{{--
 * 外部認証のメインテンプレート
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.auth.auth_manage_tab')
    </div>

    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <form name="form_message_first" method="post" action="{{url('/')}}/manage/auth/update">
            {{ csrf_field() }}

            {{-- 外部認証 --}}
            <div class="form-group">
                <label class="col-form-label">外部認証</label>
                <div class="row">
                    {{-- ラジオ表示 --}}
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio" value="0" class="custom-control-input" id="use_auth_method_0"
                                name="use_auth_method" @if(!old('use_auth_method', $use_auth_method)) checked @endif>
                            <label class="custom-control-label" for="use_auth_method_0" id="label_use_auth_method_0">
                                使用しない
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio" value="1" class="custom-control-input" id="use_auth_method_1"
                                name="use_auth_method" @if(old('use_auth_method', $use_auth_method)) checked @endif>
                            <label class="custom-control-label" for="use_auth_method_1" id="label_use_auth_method_1">
                                使用する
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 使用する外部認証 --}}
            <div class="form-group">
                <label class="col-form-label">使用する外部認証</label>
                @foreach (AuthMethodType::enum as $key => $value)
                    {{-- ラジオのチェック判定 --}}
                    @php
                        $checked = null;
                        if (old('auth_method_event', $auth_method_event) == $key) {
                            // 設定値があればそれに応じてチェックON
                            $checked = 'checked';
                        }
                    @endphp
                    {{-- ラジオ表示 --}}
                    <div class="row">
                        <div class="col">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input
                                    type="radio" value="{{ $key }}" class="custom-control-input" id="auth_method_event_{{ $key }}"
                                    name="auth_method_event" {{ $checked }}
                                >
                                <label class="custom-control-label" for="{{ "auth_method_event_${key}" }}">
                                    {{ $value }}
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="card bg-light form-text">
                    <div class="card-body px-2 pt-1 pb-1">
                        <span class="small">
                            ※ LDAP認証は <a href="{{url('/')}}/manage/auth/ldap">LDAP認証</a> 画面で設定します。<br>
                            ※ Shibboleth認証を選択すると、画面上部のログインのリンク先がShibbolethログイン画面に変更されます。<br>
                            ※ Shibboleth認証はファイルで設定します。設定ファイル：<code>config/cc_shibboleth_config.php</code><br>
                            ※ NetCommons2認証は <a href="{{url('/')}}/manage/auth/netcommons2">NetCommons2認証</a> 画面で設定します。<br>
                            ※ いずれの外部認証でも認証後、当サイトに該当ユーザがいない場合、自動作成されます。<br>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 外部認証と併せて通常ログインも使用 --}}
            <div class="form-group">
                <label class="col-form-label">通常ログインも使用</label>
                <div class="row">
                    {{-- ラジオ表示 --}}
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio" value="0" class="custom-control-input" id="use_normal_login_along_with_auth_method_0"
                                name="use_normal_login_along_with_auth_method" @if(!old('use_normal_login_along_with_auth_method', $use_normal_login_along_with_auth_method)) checked @endif>
                            <label class="custom-control-label" for="use_normal_login_along_with_auth_method_0">
                                使用しない
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio" value="1" class="custom-control-input" id="use_normal_login_along_with_auth_method_1"
                                name="use_normal_login_along_with_auth_method" @if(old('use_normal_login_along_with_auth_method', $use_normal_login_along_with_auth_method)) checked @endif>
                            <label class="custom-control-label" for="use_normal_login_along_with_auth_method_1">
                                使用する
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card bg-light form-text">
                    <div class="card-body px-2 pt-1 pb-1">
                        <span class="small">
                            ※ 「使用する」場合、外部認証毎に通常ログイン方法が異なります。<br>
                            ※ LDAP認証またはNetCommons2認証で通常ログインも「使用する」場合、外部認証でログインできなかったら、連続して通常ログインを行います。<br>
                            ※ Shibboleth認証で通常ログインも「使用する」場合、ログインURL <code>{{url('/')}}/{{config('connect.LOGIN_PATH')}}</code> を直接入力して通常ログインを行います。<br>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-form-label">注意 <span class="badge badge-danger">必須</span></label>
                <div class="row">
                    <div class="col">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="confirm_auth" value="1">以下の通常ログインに対する注意点を理解して実行します。
                            </label>
                        </div>
                        @if ($errors->has('confirm_auth'))
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-circle"></i> 通常ログインに対する注意点の確認を行ってください。
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col">
                        <div class="alert alert-warning">
                            通常ログインを「使用しない」ことで、管理機能が全て操作できなくなる危険性が発生します。<br />
                            「使用しない」に設定する場合は、外部承認に連動したユーザに、システム管理者権限を含むユーザが存在する事を確認してから実行してください。
                        </div>
                    </div>
                </div>
            </div>

            {{-- 更新ボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>

    </div>
</div>
@endsection
