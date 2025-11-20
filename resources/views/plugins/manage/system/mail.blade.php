{{--
 * システム管理のメール設定テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category システム管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.system.system_tab')
    </div>
    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        {{-- エラーメッセージ表示 --}}
        @if (session('error_message'))
            <div class="alert alert-danger">
                {{ session('error_message') }}
            </div>
        @endif

        {{-- メール設定変更時の注意喚起 --}}
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> 重要</h5>
            <p class="mb-2">メール設定を変更すると、システムからのメール送信（パスワードリセット、フォームからのメールなど）ができなくなる可能性があります。</p>
            <p class="mb-0">設定を変更する場合は、必ずシステム管理者またはサーバー管理者に確認してから行ってください。</p>
        </div>

        {{-- 認証方式選択 --}}
        <form action="{{url('/')}}/manage/system/updateMailAuthMethod" method="post" class="mb-4">
            {{ csrf_field() }}

            <div class="form-group">
                <label class="col-form-label">認証方式</label>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="mail_auth_method" id="auth_method_smtp" value="{{ MailAuthMethod::smtp }}"
                            @if($mail_auth_method == MailAuthMethod::smtp) checked @endif>
                        <label class="form-check-label" for="auth_method_smtp">
                            {{ MailAuthMethod::enum[MailAuthMethod::smtp] }}
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="mail_auth_method" id="auth_method_oauth2" value="{{ MailAuthMethod::oauth2_microsoft365_app }}"
                            @if($mail_auth_method == MailAuthMethod::oauth2_microsoft365_app) checked @endif>
                        <label class="form-check-label" for="auth_method_oauth2">
                            {{ MailAuthMethod::enum[MailAuthMethod::oauth2_microsoft365_app] }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group text-center">
                @php
                    $has_oauth2_config = Configs::getConfigsValue($oauth2_configs, 'tenant_id') ? true : false;
                    $can_switch_to_oauth2 = $has_oauth2_config && $is_oauth2_connected;
                @endphp
                <button type="submit" class="btn btn-primary" id="change_auth_method_btn"
                    data-current-method="{{ $mail_auth_method }}"
                    data-can-switch-to-oauth2="{{ $can_switch_to_oauth2 ? '1' : '0' }}"
                    data-smtp-name="{{ MailAuthMethod::enum[MailAuthMethod::smtp] }}"
                    data-oauth2-name="{{ MailAuthMethod::enum[MailAuthMethod::oauth2_microsoft365_app] }}">
                    <i class="fas fa-check"></i> 認証方式を変更
                </button>
                <div id="oauth2_config_warning" class="mt-2" style="display: none;">
                    <small class="text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="oauth2_warning_message"></span>
                    </small>
                </div>
            </div>
        </form>

        <hr>

        {{-- SMTP認証設定 --}}
        <div id="smtp_settings" style="display: {{ $mail_auth_method == MailAuthMethod::smtp ? 'block' : 'none' }};">
            <h5>SMTP認証設定</h5>
            <form action="{{url('/')}}/manage/system/updateMail" method="post">
                {{ csrf_field() }}

            <div class="form-group">
                <label class="col-form-label">送信者メールアドレス <small class="text-muted">(MAIL_FROM_ADDRESS)</small></label>
                <input type="text" name="mail_from_address" value="{{config('mail.from.address')}}" class="form-control">

            </div>

            <div class="form-group">
                <label class="col-form-label">送信者名 <small class="text-muted">(MAIL_FROM_NAME)</small></label>
                <input type="text" name="mail_from_name" value="{{config('mail.from.name')}}" class="form-control">
            </div>

            <div class="form-group">
                <label class="col-form-label">SMTPサーバアドレス <small class="text-muted">(MAIL_HOST)</small></label>
                <input type="text" name="mail_host" value="{{config('mail.host')}}" class="form-control">

            </div>

            <div class="form-group">
                <label class="col-form-label">SMTPサーバのポート番号 <small class="text-muted">(MAIL_PORT)</small></label>
                <input type="text" name="mail_port" value="{{config('mail.port')}}" class="form-control">
            </div>

            <div class="form-group">
                <label class="col-form-label">SMTPAuthのユーザ <small class="text-muted">(MAIL_USERNAME)</small></label>
                <input type="text" name="mail_username" value="{{config('mail.username')}}" class="form-control">
            </div>

            <div class="form-group">
                <label class="col-form-label">SMTPAuthのパスワード <small class="text-muted">(MAIL_PASSWORD)</small></label>
                <input type="password" name="mail_password" value="{{config('mail.password')}}" class="form-control">
            </div>

            <div class="form-group">
                <label class="col-form-label">メール暗号化 <small class="text-muted">(MAIL_ENCRYPTION)</small></label>
                <select name="mail_encryption" class="form-control">
                    <option value="null"@if(config('mail.encryption') == '' || config('mail.encryption') == 'null') selected @endif>設定なし</option>
                    <option value="tls"@if(config('mail.encryption') == 'tls') selected @endif>TLS</option>
                </select>
            </div>

            <div class="form-group">
                <label class="col-form-label">MAIL_MAILER</label>
                <div class="form-text">{{config('mail.default')}}</div>
            </div>

                {{-- Submitボタン --}}
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
                </div>
            </form>
        </div>

        {{-- Microsoft 365連携（OAuth2）設定 --}}
        <div id="oauth2_settings" style="display: {{ $mail_auth_method == MailAuthMethod::oauth2_microsoft365_app ? 'block' : 'none' }};">
            <h5>{{ MailAuthMethod::enum[MailAuthMethod::oauth2_microsoft365_app] }}設定</h5>

            @if($is_oauth2_connected)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Microsoft 365と連携済みです
                </div>
            @endif

            {{-- 初回設定時のみ折りたたみガイドを表示 --}}
            @if(!$is_oauth2_connected && !Configs::getConfigsValue($oauth2_configs, 'tenant_id'))
                <div class="card mb-3 border-info">
                    <div class="card-header bg-info text-white">
                        <a class="text-white" data-toggle="collapse" href="#oauth2_setup_guide">
                            <i class="fas fa-question-circle"></i> 初回設定の手順（クリックで開閉）
                        </a>
                    </div>
                    <div id="oauth2_setup_guide" class="collapse show">
                        <div class="card-body">
                            <ol class="mb-0">
                                <li class="mb-2"><strong>Microsoft Entra ID（旧Azure AD）でアプリを登録</strong>
                                    <ul>
                                        <li>APIアクセス許可: Microsoft Graph - Mail.Send を追加<br>
                                            <small class="text-muted">※ 「アプリケーションのアクセス許可」を選択してください</small>
                                        </li>
                                        <li>必ず管理者の同意を付与してください</li>
                                    </ul>
                                </li>
                                <li class="mb-0"><strong>取得した認証情報を入力</strong>
                                    <ul>
                                        <li>ディレクトリ (テナント) ID、アプリケーション (クライアント) ID、クライアントシークレット、送信者メールアドレスを入力</li>
                                        <li>「OAuth2設定を保存」ボタンをクリック</li>
                                        <li>設定保存と同時に自動的に連携が完了します</li>
                                    </ul>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{url('/')}}/manage/system/updateMailOauth2" method="post">
                {{ csrf_field() }}

                <div class="form-group">
                    <label class="col-form-label">ディレクトリ (テナント) ID <span class="badge badge-danger">必須</span></label>
                    <input type="text" name="tenant_id" value="{{ Configs::getConfigsValue($oauth2_configs, 'tenant_id') }}" class="form-control" required>
                    <small class="form-text text-muted">Microsoft Entra IDのディレクトリ (テナント) IDを入力してください。</small>
                </div>

                <div class="form-group">
                    <label class="col-form-label">アプリケーション (クライアント) ID <span class="badge badge-danger">必須</span></label>
                    <input type="text" name="client_id" value="{{ Configs::getConfigsValue($oauth2_configs, 'client_id') }}" class="form-control" required>
                    <small class="form-text text-muted">Microsoft Entra IDで登録したアプリケーションのアプリケーション (クライアント) IDを入力してください。</small>
                </div>

                <div class="form-group">
                    <label class="col-form-label">クライアントシークレット <span class="badge badge-danger">必須</span></label>
                    <input type="password" name="client_secret" value="" class="form-control" required>
                    <small class="form-text text-muted">Microsoft Entra IDで発行したクライアントシークレットを入力してください。（変更時のみ入力）</small>
                </div>

                <div class="form-group">
                    <label class="col-form-label">送信者メールアドレス <span class="badge badge-danger">必須</span></label>
                    <input type="email" name="mail_from_address" value="{{ Configs::getConfigsValue($oauth2_configs, 'mail_from_address') }}" class="form-control" required>
                    <small class="form-text text-muted">メール送信に使用するMicrosoft 365のメールアドレスを入力してください。</small>
                </div>

                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> OAuth2設定を保存</button>
                </div>
            </form>

            @if($is_oauth2_connected)
                {{-- 連携解除ボタン --}}
                <form action="{{url('/')}}/manage/system/mailOauth2Disconnect" method="post" class="mt-3" onsubmit="return confirm('Microsoft 365との連携を解除しますか？');">
                    {{ csrf_field() }}
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-unlink"></i> 連携を解除</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
// 認証方式に応じて表示を切り替え
document.addEventListener('DOMContentLoaded', function() {
    const smtpRadio = document.getElementById('auth_method_smtp');
    const oauth2Radio = document.getElementById('auth_method_oauth2');
    const smtpSettings = document.getElementById('smtp_settings');
    const oauth2Settings = document.getElementById('oauth2_settings');
    const changeButton = document.getElementById('change_auth_method_btn');
    const oauth2Warning = document.getElementById('oauth2_config_warning');
    const oauth2WarningMessage = document.getElementById('oauth2_warning_message');
    const currentMethod = changeButton.dataset.currentMethod;
    const canSwitchToOauth2 = changeButton.dataset.canSwitchToOauth2 === '1';
    const smtpName = changeButton.dataset.smtpName;
    const oauth2Name = changeButton.dataset.oauth2Name;

    function toggleSettings() {
        if (smtpRadio.checked) {
            smtpSettings.style.display = 'block';
            oauth2Settings.style.display = 'none';
        } else if (oauth2Radio.checked) {
            smtpSettings.style.display = 'none';
            oauth2Settings.style.display = 'block';
        }
    }

    function updateButton() {
        const selectedMethod = smtpRadio.checked ? smtpRadio.value : oauth2Radio.value;

        // 現在の設定と選択が同じ場合はボタンを無効化
        if (selectedMethod === currentMethod) {
            changeButton.disabled = true;
            oauth2Warning.style.display = 'none';
            return;
        }

        // OAuth2を選択した場合
        if (oauth2Radio.checked) {
            if (canSwitchToOauth2) {
                // 設定あり & 連携済み
                changeButton.disabled = false;
                oauth2Warning.style.display = 'none';
            } else {
                // 設定なし または 未連携
                changeButton.disabled = true;
                oauth2Warning.style.display = 'block';
                oauth2WarningMessage.textContent = oauth2Name + 'への切り替えは、OAuth2設定の保存と連携完了後に可能になります';
            }
        } else {
            // SMTPを選択した場合は常に有効
            changeButton.disabled = false;
            oauth2Warning.style.display = 'none';
        }
    }

    // ボタンクリック時の確認ダイアログ
    changeButton.form.addEventListener('submit', function(e) {
        const selectedMethod = smtpRadio.checked ? smtpRadio.value : oauth2Radio.value;

        // 現在の設定と選択が同じ場合は送信しない
        if (selectedMethod === currentMethod) {
            e.preventDefault();
            return false;
        }

        // SMTPに戻す場合は確認
        if (smtpRadio.checked && currentMethod !== smtpRadio.value) {
            if (!confirm(smtpName + 'に戻しますか？\n\n' + oauth2Name + 'から' + smtpName + 'に切り替えます。')) {
                e.preventDefault();
                return false;
            }
        }

        // OAuth2に切り替える場合も確認
        if (oauth2Radio.checked && currentMethod !== oauth2Radio.value) {
            if (!confirm(oauth2Name + 'に切り替えますか？\n\n' + smtpName + 'から' + oauth2Name + 'に切り替えます。')) {
                e.preventDefault();
                return false;
            }
        }
    });

    smtpRadio.addEventListener('change', function() {
        toggleSettings();
        updateButton();
    });

    oauth2Radio.addEventListener('change', function() {
        toggleSettings();
        updateButton();
    });

    // 初期状態を設定
    updateButton();
});
</script>

@endsection
