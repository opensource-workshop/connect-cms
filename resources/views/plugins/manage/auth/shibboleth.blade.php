{{--
 * 外部認証>Shibboleth認証
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

        <div class="alert alert-info">
            <i class="fas fa-exclamation-circle"></i> Shibboleth認証はファイルで設定します。設定ファイル：<code>config/cc_shibboleth_config.php</code><br />
            <i class="fas fa-exclamation-circle"></i> Shibboleth認証はApacheの <code>mod_shib</code> モジュールを利用します。<br />
            <span class="ml-4"><code>mod_shib</code> モジュールによって、 ShibbolethのユーザID・ユーザ名・メールアドレス等が<code>$_SERVER</code> 変数にセットされます。</span><br />
        </div>

        <form name="form_auth" id="form_auth" class="form-horizontal" method="post" action="{{url('/')}}/manage/auth/ldapUpdate">
            {{ csrf_field() }}

            <label class="col-form-label">現在の設定</label>
            <div class="form-group card border-info">
                <div class="card-body p-0">

                    <dl class="m-3">
                        <dt>login_path</dt>
                        <dd>
                            {{  config('cc_shibboleth_config.login_path') ?? '未設定'  }}<br />
                            <small class="form-text text-muted">
                                ※ Shibboleth認証のログインパスです。<br />
                            </small>
                        </dd>
                        <dt>userid</dt>
                        <dd>
                            {{  config('cc_shibboleth_config.userid') ?? '未設定' }}<br />
                            <small class="form-text text-muted">
                                ※ 当設定値を利用して <code>$_SERVER</code> 変数からShibbolethの「ユーザID」を取得します。<br />
                                ※ 取得した値は、ユーザの自動作成と自動ログインに使用します。<br />
                            </small>
                        </dd>
                        <dt>user_name</dt>
                        <dd>
                            {{  config('cc_shibboleth_config.user_name') ?? '未設定' }}<br />
                            <small class="form-text text-muted">
                                ※ 当設定値を利用して <code>$_SERVER</code> 変数からShibbolethの「ユーザ名」を取得します。<br />
                                ※ 取得した値は、ユーザの自動作成に使用します。<br />
                            </small>
                        </dd>
                        <dt>user_email</dt>
                        <dd>
                            {{  config('cc_shibboleth_config.user_email') ?? '未設定' }}<br />
                            <small class="form-text text-muted">
                                ※ 当設定値を利用して <code>$_SERVER</code> 変数からShibbolethの「メールアドレス」を取得します。<br />
                                ※ 取得した値は、ユーザの自動作成に使用します。<br />
                            </small>
                        </dd>
                    </dl>

                </div>
            </div>

        </form>
    </div>
</div>

@endsection
