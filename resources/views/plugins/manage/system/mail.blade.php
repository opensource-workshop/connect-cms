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
                <label class="col-form-label">MAIL_DRIVER</label>
                <div class="form-text">{{config('mail.driver')}}</div>
            </div>

            {{-- Submitボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>

        </form>
    </div>

</div>

@endsection
