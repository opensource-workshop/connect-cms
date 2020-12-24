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
                                type="radio" value="1" class="custom-control-input" id="use_auth_method_1" 
                                name="use_auth_method" @if($use_auth_method) checked @endif>
                            <label class="custom-control-label" for="use_auth_method_1">
                                使用する
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input 
                                type="radio" value="0" class="custom-control-input" id="use_auth_method_0" 
                                name="use_auth_method" @if(!$use_auth_method) checked @endif>
                            <label class="custom-control-label" for="use_auth_method_0">
                                使用しない
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
                        if($auth_method == $key){
                            // 設定値があればそれに応じてチェックON
                            $checked = 'checked';
                        }
                    @endphp
                    {{-- ラジオ表示 --}}
                    <div class="row">
                        <div class="col">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input 
                                    type="radio" value="{{ $key }}" class="custom-control-input" id="auth_method_{{ $key }}" 
                                    name="auth_method" {{ $checked }}
                                >
                                <label class="custom-control-label" for="{{ "auth_method_${key}" }}">
                                    {{ $value }}
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
                <small class="form-text text-muted">
                    ※ NetCommons2認証は <a href="{{url('/')}}/manage/auth/netcommons2">NetCommons2認証</a> 画面で設定します。<br>
                    ※ Shibboleth認証を選択すると、画面上部のログインのリンク先がShibbolethログイン画面に変更されます。<br>
                    ※ Shibboleth認証はファイルで設定します。設定ファイル：<code>config/cc_shibboleth_config.php</code><br>
                </small>
            </div>

            {{-- 更新ボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>

    </div>
</div>
@endsection
