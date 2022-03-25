{{--
 * 自動ユーザ登録設定画面のテンプレート
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

        @include('plugins.common.errors_form_line')

        <form action="{{url('/')}}/manage/user/autoRegistUpdate" method="POST">
            {{csrf_field()}}

            {{-- 自動ユーザ登録の使用 --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">自動ユーザ登録の使用</label>
                <div class="col pt-0">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "user_register_enable") == "1")
                            <input type="radio" value="1" id="user_register_enable_on" name="user_register_enable" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="user_register_enable_on" name="user_register_enable" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="user_register_enable_on" id="label_user_register_enable_on">許可する</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "user_register_enable") == "0")
                            <input type="radio" value="0" id="user_register_enable_off" name="user_register_enable" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="user_register_enable_off" name="user_register_enable" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="user_register_enable_off" id="label_user_register_enable_off">許可しない</label>
                    </div>
                    <small class="form-text text-muted">自動ユーザ登録を使用するかどうかを選択</small>
                </div>
            </div>

            {{-- 自動ユーザ登録時に以下のアドレスにメール送信する --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">メール送信先</label>
                <div class="col pt-0">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="user_register_mail_send_flag" value="0">
                        @if(Configs::getConfigsValueAndOld($configs, "user_register_mail_send_flag") == "1")
                            <input name="user_register_mail_send_flag" value="1" type="checkbox" class="custom-control-input" id="user_register_mail_send_flag" checked="checked">
                        @else
                            <input name="user_register_mail_send_flag" value="1" type="checkbox" class="custom-control-input" id="user_register_mail_send_flag">
                        @endif
                        <label class="custom-control-label" for="user_register_mail_send_flag">以下のアドレスにメール送信する</label>
                    </div>
                </div>
            </div>

            {{-- 自動ユーザ登録時に送信するメールアドレス --}}
            <div class="form-group row">
                <div class="col-md-3"></div>
                <div class="col">
                    <label class="col-form-label">送信するメールアドレス（複数ある場合はカンマで区切る）</label>
                    <input type="text" name="user_register_mail_send_address" value="{{Configs::getConfigsValueAndOld($configs, 'user_register_mail_send_address')}}" class="form-control">
                    <small class="form-text text-muted">自動ユーザ登録時に管理者や担当者等に通知するメールアドレスを設定</small>
                    @if ($errors && $errors->has('user_register_mail_send_address')) <div class="text-danger">{{$errors->first('user_register_mail_send_address')}}</div> @endif
                </div>
            </div>

            {{-- 自動ユーザ登録時に登録者にメール送信する --}}
            <div class="form-group row">
                <div class="col-md-3"></div>
                <div class="col">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="user_register_user_mail_send_flag" value="0">
                        @if(Configs::getConfigsValueAndOld($configs, "user_register_user_mail_send_flag") == "1")
                            <input name="user_register_user_mail_send_flag" value="1" type="checkbox" class="custom-control-input" id="user_register_user_mail_send_flag"checked="checked">
                        @else
                            <input name="user_register_user_mail_send_flag" value="1" type="checkbox" class="custom-control-input" id="user_register_user_mail_send_flag">
                        @endif
                        <label class="custom-control-label" for="user_register_user_mail_send_flag">登録者にメール送信する</label>
                    </div>
                    @if ($errors && $errors->has('user_register_user_mail_send_flag')) <div class="text-danger">{{$errors->first('user_register_user_mail_send_flag')}}</div> @endif
                </div>
            </div>


            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">仮登録メール</label>
                <div class="col">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="user_register_temporary_regist_mail_flag" value="0">
                        @if(Configs::getConfigsValueAndOld($configs, "user_register_temporary_regist_mail_flag") == "1")
                            <input type="checkbox" name="user_register_temporary_regist_mail_flag" value="1" class="custom-control-input" id="user_register_temporary_regist_mail_flag" checked=checked>
                        @else
                            <input type="checkbox" name="user_register_temporary_regist_mail_flag" value="1" class="custom-control-input" id="user_register_temporary_regist_mail_flag">
                        @endif
                        <label class="custom-control-label" for="user_register_temporary_regist_mail_flag">登録者に仮登録メールを送信する</label>
                    </div>
                    <div>
                        <small class="text-muted">
                            ※ 仮登録メールを使う事で、本登録前にメールアドレスの確認がとれます。<br>
                            ※ 仮登録メールを使うには、「登録者にメール送信する」のチェックを付けてください。また「仮登録メールフォーマット」に [[entry_url]] を含めてください。
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right"></label>
                <div class="col">
                    <label class="control-label">仮登録メール件名</label>
                    <input type="text" name="user_register_temporary_regist_mail_subject" value="{{Configs::getConfigsValueAndOld($configs, 'user_register_temporary_regist_mail_subject')}}" class="form-control" placeholder="（例）仮登録のお知らせと本登録のお願い">
                    <small class="text-muted">
                        ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right"></label>
                <div class="col">
                    <label class="control-label">仮登録メールフォーマット</label>
                    <textarea name="user_register_temporary_regist_mail_format" class="form-control" rows=5 placeholder="（例）ユーザ仮登録を受け付けました。&#13;&#10;引き続き、下記のURLへアクセスしていただき、ユーザ本登録を行ってください。&#13;&#10;&#13;&#10;↓ユーザ本登録URL&#13;&#10;[[entry_url]]&#13;&#10;&#13;&#10;※お使いのメールソフトによっては、URLが途中で切れてアクセスできない場合があります。&#13;&#10;　その場合はクリックされるのではなくURLをブラウザのアドレス欄にコピー＆ペーストしてアクセスしてください。&#13;&#10;----------------------------------&#13;&#10;[[body]]&#13;&#10;----------------------------------">{{Configs::getConfigsValueAndOld($configs, "user_register_temporary_regist_mail_format")}}</textarea>
                    <small class="text-muted">
                        ※ [[entry_url]] を記述すると本登録URLが入ります。本登録URLの有効期限は仮登録後60分です。<br>
                        ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                        ※ [[body]] を記述すると該当部分に登録内容が入ります。
                    </small>
                    @if ($errors && $errors->has('user_register_temporary_regist_mail_format')) <div class="text-danger">{{$errors->first('user_register_temporary_regist_mail_format')}}</div> @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">仮登録後のメッセージ</label>
                <div class="col">
                    <input type="text" name="user_register_temporary_regist_after_message" value="{{Configs::getConfigsValueAndOld($configs, 'user_register_temporary_regist_after_message')}}" class="form-control">
                    <small class="text-muted">※ （例）ユーザを仮登録しました。メールを送信しましたので、記載されているリンクより登録を完了してください。</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">本登録メール</label>
                <div class="col">
                    <label class="control-label">本登録メール件名</label>
                    <input type="text" name="user_register_mail_subject" value="{{Configs::getConfigsValueAndOld($configs, 'user_register_mail_subject')}}" class="form-control">
                    <small class="text-muted">
                        ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right"></label>
                <div class="col">
                    <label class="control-label">本登録メールフォーマット</label>
                    <textarea name="user_register_mail_format" class="form-control" rows=5 placeholder="（例）登録内容をお知らせいたします。&#13;&#10;----------------------------------&#13;&#10;[[body]]&#13;&#10;----------------------------------">{{Configs::getConfigsValueAndOld($configs, 'user_register_mail_format')}}</textarea>
                    <small class="text-muted">
                        ※ [[site_name]] を記述すると該当部分にサイト名が入ります。<br>
                        ※ [[body]] を記述すると該当部分に登録内容が入ります。<br>
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">本登録後のメッセージ</label>
                <div class="col">
                    <input type="text" name="user_register_after_message" value="{{Configs::getConfigsValueAndOld($configs, 'user_register_after_message')}}" class="form-control">
                    <small class="text-muted">※ （例）ユーザ登録が完了しました。登録したログインID、パスワードでログインしてください。</small>
                </div>
            </div>


            {{-- 自動ユーザ登録時に個人情報保護方針への同意を求めるか --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">個人情報保護方針への同意</label>
                <div class="col-md-9">
                    <div class="custom-control custom-checkbox">
                        <input type="hidden" name="user_register_requre_privacy" value="0">
                        @if(Configs::getConfigsValueAndOld($configs, "user_register_requre_privacy") == "1")
                            <input name="user_register_requre_privacy" value="1" type="checkbox" class="custom-control-input" id="user_register_requre_privacy" checked="checked">
                        @else
                            <input name="user_register_requre_privacy" value="1" type="checkbox" class="custom-control-input" id="user_register_requre_privacy">
                        @endif
                        <label class="custom-control-label" for="user_register_requre_privacy">同意を求める</label>
                    </div>
                    <small class="form-text text-muted">自動ユーザ登録時に個人情報保護方針への同意を求めるか設定</small>
                </div>
            </div>

            {{-- 自動ユーザ登録時に求める個人情報保護方針の表示内容 --}}
            <div class="form-group row">
                <div class="col-md-3"></div>
                <div class="col">
                    <label class="col-form-label">個人情報保護方針の表示内容</label>
                    <textarea name="user_register_privacy_description" class="form-control" rows=3>{!!Configs::getConfigsValueAndOld($configs, "user_register_privacy_description")!!}</textarea>
                    <small class="form-text text-muted">自動ユーザ登録時に求める個人情報保護方針への説明文</small>
                </div>
            </div>

            {{-- 自動ユーザ登録時に求めるユーザ登録についての文言 --}}
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">ユーザ登録について</label>
                <div class="col-md-9">
                    <textarea name="user_register_description" class="form-control" rows=3>{!!Configs::getConfigsValueAndOld($configs, "user_register_description")!!}</textarea>
                    <small class="form-text text-muted">自動ユーザ登録時に求めるユーザ登録についての説明文</small>
                </div>
            </div>

            {{-- 初期コンテンツ権限 --}}
            @php
                $base_roles = [];
                $use_base_role_env = false;

                // envの設定を優先して利用する
                if (config('connect.SELF_REGISTER_BASE_ROLES') !== null) {
                    $base_roles = explode(',', config('connect.SELF_REGISTER_BASE_ROLES'));
                    $use_base_role_env = true;
                } else {
                    $base_roles = explode(',', Configs::getConfigsValue($configs, "user_register_base_roles"));
                    if (old('base_roles') !== null && is_array(old('base_roles'))) {
                        $base_roles = old('base_roles');
                    }
                }
            @endphp
            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">初期コンテンツ権限</label>
                <div class="col-md-9">
                    <input type="hidden" name="base_roles[]" value="">
                    <div class="custom-control custom-checkbox">
                        <input name="base_roles[]" value="role_article_admin" type="checkbox" class="custom-control-input" id="role_article_admin"
                            @if (in_array('role_article_admin', $base_roles)) checked="checked" @endif
                            @if ($use_base_role_env) disabled="disabled" @endif
                        >
                        <label class="custom-control-label" for="role_article_admin" id="label_role_article_admin">コンテンツ管理者</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input name="base_roles[]" value="role_arrangement" type="checkbox" class="custom-control-input" id="role_arrangement"
                            @if (in_array('role_arrangement', $base_roles))  checked="checked" @endif
                            @if ($use_base_role_env) disabled="disabled" @endif
                        >
                        <label class="custom-control-label" for="role_arrangement" id="label_role_arrangement">プラグイン管理者</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input name="base_roles[]" value="role_article" type="checkbox" class="custom-control-input" id="role_article"
                            @if (in_array('role_article', $base_roles))  checked="checked" @endif
                            @if ($use_base_role_env) disabled="disabled" @endif
                        >
                        <label class="custom-control-label" for="role_article" id="label_role_article">モデレータ（他ユーザの記事も更新）</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input name="base_roles[]" value="role_approval" type="checkbox" class="custom-control-input" id="role_approval"
                            @if (in_array('role_approval', $base_roles))  checked="checked" @endif
                            @if ($use_base_role_env) disabled="disabled" @endif
                        >
                        <label class="custom-control-label" for="role_approval" id="label_role_approval">承認者</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input name="base_roles[]" value="role_reporter" type="checkbox" class="custom-control-input" id="role_reporter"
                            @if (in_array('role_reporter', $base_roles))  checked="checked" @endif
                            @if ($use_base_role_env) disabled="disabled" @endif
                        >
                        <label class="custom-control-label" for="role_reporter" id="label_role_reporter">編集者</label>
                    </div>
                    <small class="text-muted">
                        ※「編集者」、「モデレータ」の記事投稿については、各プラグイン側の権限設定も必要です。<br />
                        ※「コンテンツ管理者」は、「コンテンツ管理者」権限と同時に「プラグイン管理者」「モデレータ」「承認者」「編集者」権限も併せて持ちます。<br />
                        ※ 全てのユーザは、「ゲスト」権限も併せて持ちます。<br />
                    </small>
                </div>
            </div>

            {{-- Submitボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>
    </div>
</div>
@endsection
