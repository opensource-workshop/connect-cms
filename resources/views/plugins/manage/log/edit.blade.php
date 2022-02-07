{{--
 * ログ管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ログ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.log.log_tab')
    </div>

    <div class="card-body">
        <form name="form_edit" method="post" action="{{url('/')}}/manage/log/update">
            {{ csrf_field() }}

            {{-- 記録レベルの設定 --}}
            <div class="form-group">
                <label class="col-form-label">記録範囲</label>
                <div class="row">
                    <div class="col-md-2">
                        <div class="custom-control custom-radio custom-control-inline">
                            @if(isset($configs["app_log_scope"]) && $configs["app_log_scope"] == "all")
                                <input type="radio" value="all" id="app_log_scope_all" name="app_log_scope" class="custom-control-input" data-toggle="collapse" data-target="#collapse_save_log_select.show" aria-expanded="true" aria-controls="collapse_save_log_select" checked="checked">
                            @else
                                <input type="radio" value="all" id="app_log_scope_all" name="app_log_scope" class="custom-control-input" data-toggle="collapse" data-target="#collapse_save_log_select.show" aria-expanded="true" aria-controls="collapse_save_log_select">
                            @endif
                            <label class="custom-control-label" for="app_log_scope_all" id="app_log_scope_all_label">全て</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-radio custom-control-inline">
                            {{-- 記録範囲が select もしくは、空ならば、選択したもののみ --}}
                            @if((isset($configs["app_log_scope"]) && $configs["app_log_scope"] == "select") || !isset($configs["app_log_scope"]))
                                <input type="radio" value="select" id="app_log_scope_select" name="app_log_scope" class="custom-control-input" data-toggle="collapse" data-target="#collapse_save_log_select:not(.show)" aria-expanded="true" aria-controls="collapse_save_log_select" checked="checked">
                            @else
                                <input type="radio" value="select" id="app_log_scope_select" name="app_log_scope" class="custom-control-input" data-toggle="collapse" data-target="#collapse_save_log_select:not(.show)" aria-expanded="true" aria-controls="collapse_save_log_select">
                            @endif
                            <label class="custom-control-label" for="app_log_scope_select" id="app_log_scope_select_label">選択したもののみ</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 記録するログ種別の選択 --}}
            @if(isset($configs["app_log_scope"]) && $configs["app_log_scope"] == "all")
            <div class="form-group collapse collapse_save_log_select" id="collapse_save_log_select">
            @else
            <div class="form-group collapse collapse_save_log_select show" id="collapse_save_log_select">
            @endif
                <label class="col-form-label">記録するログ種別</label><small class="text-muted">（ひとつでも合致すれば記録します）</small>
                <div class="row">
                    <label class="col-md-3 mt-3 mt-md-0 text-md-right">ログイン関係</label>
                    <div class="col-md-9">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_login"]) && $configs["save_log_type_login"] == "1")
                                <input name="save_log_type_login" value="1" type="checkbox" class="custom-control-input" id="save_log_type_login" checked>
                            @else
                                <input name="save_log_type_login" value="1" type="checkbox" class="custom-control-input" id="save_log_type_login">
                            @endif
                            <label class="custom-control-label" for="save_log_type_login" id="save_log_type_login_label">ログイン・ログアウト</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_authed"]) && $configs["save_log_type_authed"] == "1")
                                <input name="save_log_type_authed" value="1" type="checkbox" class="custom-control-input" id="save_log_type_authed" checked>
                            @else
                                <input name="save_log_type_authed" value="1" type="checkbox" class="custom-control-input" id="save_log_type_authed">
                            @endif
                            <label class="custom-control-label" for="save_log_type_authed" id="save_log_type_authed_label">ログイン後のページ操作</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 mt-3 mt-md-0 text-md-right">種別</label>
                    <div class="col-md-9">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_page"]) && $configs["save_log_type_page"] == "1")
                                <input name="save_log_type_page" value="1" type="checkbox" class="custom-control-input" id="save_log_type_page" checked>
                            @else
                                <input name="save_log_type_page" value="1" type="checkbox" class="custom-control-input" id="save_log_type_page">
                            @endif
                            <label class="custom-control-label" for="save_log_type_page" id="save_log_type_page_label">一般ページ</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_manage"]) && $configs["save_log_type_manage"] == "1")
                                <input name="save_log_type_manage" value="1" type="checkbox" class="custom-control-input" id="save_log_type_manage" checked>
                            @else
                                <input name="save_log_type_manage" value="1" type="checkbox" class="custom-control-input" id="save_log_type_manage">
                            @endif
                            <label class="custom-control-label" for="save_log_type_manage" id="save_log_type_manage_label">管理画面</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_mypage"]) && $configs["save_log_type_mypage"] == "1")
                                <input name="save_log_type_mypage" value="1" type="checkbox" class="custom-control-input" id="save_log_type_mypage" checked>
                            @else
                                <input name="save_log_type_mypage" value="1" type="checkbox" class="custom-control-input" id="save_log_type_mypage">
                            @endif
                            <label class="custom-control-label" for="save_log_type_mypage" id="save_log_type_mypage_label">マイページ</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_api"]) && $configs["save_log_type_api"] == "1")
                                <input name="save_log_type_api" value="1" type="checkbox" class="custom-control-input" id="save_log_type_api" checked>
                            @else
                                <input name="save_log_type_api" value="1" type="checkbox" class="custom-control-input" id="save_log_type_api">
                            @endif
                            <label class="custom-control-label" for="save_log_type_api" id="save_log_type_api_label">API</label>
                        </div><br />

                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_search_keyword"]) && $configs["save_log_type_search_keyword"] == "1")
                                <input name="save_log_type_search_keyword" value="1" type="checkbox" class="custom-control-input" id="save_log_type_search_keyword" checked>
                            @else
                                <input name="save_log_type_search_keyword" value="1" type="checkbox" class="custom-control-input" id="save_log_type_search_keyword">
                            @endif
                            <label class="custom-control-label" for="save_log_type_search_keyword" id="save_log_type_search_keyword_label">検索キーワード</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_sendmail"]) && $configs["save_log_type_sendmail"] == "1")
                                <input name="save_log_type_sendmail" value="1" type="checkbox" class="custom-control-input" id="save_log_type_sendmail" checked>
                            @else
                                <input name="save_log_type_sendmail" value="1" type="checkbox" class="custom-control-input" id="save_log_type_sendmail">
                            @endif
                            <label class="custom-control-label" for="save_log_type_sendmail" id="save_log_type_sendmail_label">メール送信</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_passwordpage"]) && $configs["save_log_type_passwordpage"] == "1")
                                <input name="save_log_type_passwordpage" value="1" type="checkbox" class="custom-control-input" id="save_log_type_passwordpage" checked>
                            @else
                                <input name="save_log_type_passwordpage" value="1" type="checkbox" class="custom-control-input" id="save_log_type_passwordpage">
                            @endif
                            <label class="custom-control-label" for="save_log_type_passwordpage" id="save_log_type_passwordpage_label">パスワードページ認証</label>
                        </div><br />

                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_download"]) && $configs["save_log_type_download"] == "1")
                                <input name="save_log_type_download" value="1" type="checkbox" class="custom-control-input" id="save_log_type_download" checked>
                            @else
                                <input name="save_log_type_download" value="1" type="checkbox" class="custom-control-input" id="save_log_type_download">
                            @endif
                            <label class="custom-control-label" for="save_log_type_download" id="save_log_type_download_label">ダウンロード</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_css"]) && $configs["save_log_type_css"] == "1")
                                <input name="save_log_type_css" value="1" type="checkbox" class="custom-control-input" id="save_log_type_css" checked>
                            @else
                                <input name="save_log_type_css" value="1" type="checkbox" class="custom-control-input" id="save_log_type_css">
                            @endif
                            <label class="custom-control-label" for="save_log_type_css" id="save_log_type_css_label">CSS</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_file"]) && $configs["save_log_type_file"] == "1")
                                <input name="save_log_type_file" value="1" type="checkbox" class="custom-control-input" id="save_log_type_file" checked>
                            @else
                                <input name="save_log_type_file" value="1" type="checkbox" class="custom-control-input" id="save_log_type_file">
                            @endif
                            <label class="custom-control-label" for="save_log_type_file" id="save_log_type_file_label">ファイル</label>
                        </div><br />

                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_password"]) && $configs["save_log_type_password"] == "1")
                                <input name="save_log_type_password" value="1" type="checkbox" class="custom-control-input" id="save_log_type_password" checked>
                            @else
                                <input name="save_log_type_password" value="1" type="checkbox" class="custom-control-input" id="save_log_type_password">
                            @endif
                            <label class="custom-control-label" for="save_log_type_password" id="save_log_type_password_label">パスワード関係</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_register"]) && $configs["save_log_type_register"] == "1")
                                <input name="save_log_type_register" value="1" type="checkbox" class="custom-control-input" id="save_log_type_register" checked>
                            @else
                                <input name="save_log_type_register" value="1" type="checkbox" class="custom-control-input" id="save_log_type_register">
                            @endif
                            <label class="custom-control-label" for="save_log_type_register" id="save_log_type_register_label">ユーザ登録</label>
                        </div><br />

                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_core"]) && $configs["save_log_type_core"] == "1")
                                <input name="save_log_type_core" value="1" type="checkbox" class="custom-control-input" id="save_log_type_core" checked>
                            @else
                                <input name="save_log_type_core" value="1" type="checkbox" class="custom-control-input" id="save_log_type_core">
                            @endif
                            <label class="custom-control-label" for="save_log_type_core" id="save_log_type_core_label">コア側処理</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_language"]) && $configs["save_log_type_language"] == "1")
                                <input name="save_log_type_language" value="1" type="checkbox" class="custom-control-input" id="save_log_type_language" checked>
                            @else
                                <input name="save_log_type_language" value="1" type="checkbox" class="custom-control-input" id="save_log_type_language">
                            @endif
                            <label class="custom-control-label" for="save_log_type_language" id="save_log_type_language_label">言語切り替え</label>
                        </div><br />
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 mt-3 mt-md-0 text-md-right">HTTPメソッド</label>
                    <div class="col-md-9">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_http_get"]) && $configs["save_log_type_http_get"] == "1")
                                <input name="save_log_type_http_get" value="1" type="checkbox" class="custom-control-input" id="save_log_type_http_get" checked>
                            @else
                                <input name="save_log_type_http_get" value="1" type="checkbox" class="custom-control-input" id="save_log_type_http_get">
                            @endif
                            <label class="custom-control-label" for="save_log_type_http_get" id="save_log_type_http_get_label">GET</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_http_post"]) && $configs["save_log_type_http_post"] == "1")
                                <input name="save_log_type_http_post" value="1" type="checkbox" class="custom-control-input" id="save_log_type_http_post" checked>
                            @else
                                <input name="save_log_type_http_post" value="1" type="checkbox" class="custom-control-input" id="save_log_type_http_post">
                            @endif
                            <label class="custom-control-label" for="save_log_type_http_post" id="save_log_type_http_post_label">POST</label>
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
