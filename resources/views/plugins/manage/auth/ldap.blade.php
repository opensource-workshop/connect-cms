{{--
 * 外部認証>LDAP認証
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
        @include('plugins.common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <form name="form_auth" id="form_auth" class="form-horizontal" method="post" action="{{url('/')}}/manage/auth/ldapUpdate">
            {{ csrf_field() }}

            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">LDAP URI</label>
                    <input type="text" name="auth_ldap_uri" value="{{old('auth_ldap_uri', $config->additional1)}}" class="form-control @if ($errors->has('auth_ldap_uri')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'auth_ldap_uri'])
                    <small class="form-text text-muted">
                        ※ <code>ldap://hostname:port</code> 形式、あるいは <code>ldaps://hostname:port</code> 形式が使えます。<br />
                        ※ 設定例）ldap://localhost:389<br />
                    </small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-form-label">DNタイプ</label>
                <div class="row">

                    @foreach (AuthLdapDnType::enum as $key => $value)
                        {{-- ラジオのチェック判定 --}}
                        @php
                            $checked = null;
                            if (AuthLdapDnType::dn == $key && empty(old('auth_ldap_dn_type', $config->additional2))) {
                                // （初期値）DNで値空ならチェック
                                $checked = 'checked';
                            }
                            if (old('auth_ldap_dn_type', $config->additional2) == $key) {
                                // 設定値があればそれに応じてチェックON
                                $checked = 'checked';
                            }
                        @endphp
                        {{-- ラジオ表示 --}}
                        <div class="col-md-3">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" value="{{ $key }}" class="custom-control-input" id="auth_ldap_dn_type_{{ $key }}" name="auth_ldap_dn_type" {{ $checked }} >
                                <label class="custom-control-label" for="auth_ldap_dn_type_{{ $key }}">
                                    {!! $value !!}
                                </label>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            <div class="form-group row">
                <div class="col">
                    <label class="col-form-label">DN</label>
                    <input type="text" name="auth_ldap_dn" value="{{old('auth_ldap_dn', $config->additional3)}}" class="form-control">
                    <small class="form-text text-muted">
                        ※ DNタイプ「DN」の設定例）ou=People,dc=example,dc=com<br />
                        ※ DNタイプ「Active Directory」の設定例）example.com<br />
                    </small>
                </div>
            </div>

            <label class="col-form-label">現在の状態</label>
            <div class="form-group card border-info">
                <div class="card-body p-0">

                    <dl class="m-3">
                        <dt>php_ldap</dt>
                        <dd>
                            @if (function_exists('ldap_connect'))
                                有効です。LDAP認証を利用できます。
                            @else
                                <span class="text-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                無効なため、LDAP認証を利用できません。利用するにはPHP設定を変更してphp_ldapを有効にしてください。
                            @endif
                        </dd>
                    </dl>

                </div>
            </div>

            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>
    </div>
</div>

@endsection
