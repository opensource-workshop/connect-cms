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
                                <input type="radio" value="all" id="app_log_scope_all" name="app_log_scope" class="custom-control-input" checked="checked">
                            @else
                                <input type="radio" value="all" id="app_log_scope_all" name="app_log_scope" class="custom-control-input">
                            @endif
                            <label class="custom-control-label" for="app_log_scope_all">全て</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-radio custom-control-inline">
                            {{-- 記録範囲が select もしくは、空ならば、選択したもののみ --}}
                            @if((isset($configs["app_log_scope"]) && $configs["app_log_scope"] == "select") || !isset($configs["app_log_scope"]))
                                <input type="radio" value="select" id="app_log_scope_select" name="app_log_scope" class="custom-control-input" checked="checked">
                            @else
                                <input type="radio" value="select" id="app_log_scope_select" name="app_log_scope" class="custom-control-input">
                            @endif
                            <label class="custom-control-label" for="app_log_scope_select">選択したもののみ</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 記録するログ種別の選択 --}}
            <div class="form-group">
                <label class="col-form-label">記録するログ種別</label>
                <div class="row">
                    <div class="col-md">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_login"]) && $configs["save_log_type_login"] == "1")
                                <input name="save_log_type_login" value="1" type="checkbox" class="custom-control-input" id="save_log_type_login" checked>
                            @else
                                <input name="save_log_type_login" value="1" type="checkbox" class="custom-control-input" id="save_log_type_login">
                            @endif
                            <label class="custom-control-label" for="save_log_type_login">ログイン操作</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_authed"]) && $configs["save_log_type_authed"] == "1")
                                <input name="save_log_type_authed" value="1" type="checkbox" class="custom-control-input" id="save_log_type_authed" checked>
                            @else
                                <input name="save_log_type_authed" value="1" type="checkbox" class="custom-control-input" id="save_log_type_authed">
                            @endif
                            <label class="custom-control-label" for="save_log_type_authed">ログイン後のページ操作</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_search_keyword"]) && $configs["save_log_type_search_keyword"] == "1")
                                <input name="save_log_type_search_keyword" value="1" type="checkbox" class="custom-control-input" id="save_log_type_search_keyword" checked>
                            @else
                                <input name="save_log_type_search_keyword" value="1" type="checkbox" class="custom-control-input" id="save_log_type_search_keyword">
                            @endif
                            <label class="custom-control-label" for="save_log_type_search_keyword">検索キーワード</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if(isset($configs["save_log_type_search_keyword"]) && $configs["save_log_type_search_keyword"] == "1")
                                <input name="save_log_type_sendmail" value="1" type="checkbox" class="custom-control-input" id="save_log_type_sendmail" checked>
                            @else
                                <input name="save_log_type_sendmail" value="1" type="checkbox" class="custom-control-input" id="save_log_type_sendmail">
                            @endif
                            <label class="custom-control-label" for="save_log_type_sendmail">メール送信</label>
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
