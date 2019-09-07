{{--
 * サイト管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- 機能選択タブ --}}
@include('plugins.manage.site.site_manage_tab')

<div class="card">
<div class="card-body">

    <form action="/manage/site/update" method="POST">
    {{csrf_field()}}

        {{-- サイト名 --}}
        <div class="form-group">
            <label class="col-form-label">サイト名</label>
            <input type="text" name="base_site_name" value="{{$configs["base_site_name"]}}" class="form-control">
            <small class="form-text text-muted">サイト名（各ページで上書き可能 ※予定）</small>
        </div>

        {{-- テーマ --}}
        <div class="form-group">
            <label class="col-form-label">テーマ</label>
            <input type="text" name="base_theme" value="{{$configs["base_theme"]}}" class="form-control">
            <small class="form-text text-muted">画面の基本のテーマ（各ページで上書き可能）</small>
        </div>

        {{-- 背景色 --}}
        <div class="form-group">
            <label class="col-form-label">背景色</label>
            <input type="text" name="base_background_color" value="{{$configs["base_background_color"]}}" class="form-control">
            <small class="form-text text-muted">画面の基本の背景色（各ページで上書き可能）</small>
        </div>

        {{-- ヘッダーの背景色 --}}
        <div class="form-group">
            <label class="col-form-label">ヘッダーの背景色</label>
            <input type="text" name="base_header_color" value="{{$configs["base_header_color"]}}" class="form-control">
            <small class="form-text text-muted">画面の基本のヘッダー背景色（各ページで上書き可能）</small>
        </div>

        {{-- ヘッダーの固定指定 --}}
        <div class="form-group">
            <label class="col-form-label">ヘッダーの固定</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["base_header_fix_xs"]) && $configs["base_header_fix_xs"] == "1")
                            <input name="base_header_fix_xs" value="1" type="checkbox" class="custom-control-input" id="base_header_fix_xs" checked="checked">
                        @else
                            <input name="base_header_fix_xs" value="1" type="checkbox" class="custom-control-input" id="base_header_fix_xs">
                        @endif
                        <label class="custom-control-label" for="base_header_fix_xs">スマートフォン</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["base_header_fix_sm"]) && $configs["base_header_fix_sm"] == "1")
                            <input name="base_header_fix_sm" value="1" type="checkbox" class="custom-control-input" id="base_header_fix_sm" checked="checked">
                        @else
                            <input name="base_header_fix_sm" value="1" type="checkbox" class="custom-control-input" id="base_header_fix_sm">
                        @endif
                        <label class="custom-control-label" for="base_header_fix_sm">タブレット</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["base_header_fix_md"]) && $configs["base_header_fix_md"] == "1")
                            <input name="base_header_fix_md" value="1" type="checkbox" class="custom-control-input" id="base_header_fix_md" checked="checked">
                        @else
                            <input name="base_header_fix_md" value="1" type="checkbox" class="custom-control-input" id="base_header_fix_md">
                        @endif
                        <label class="custom-control-label" for="base_header_fix_md">PC</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">ヘッダーを固定にするサイズをチェック</small>
        </div>

        {{-- ログインリンクの表示 --}}
        <div class="form-group">
            <label class="col-form-label">ログインリンクの表示</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["base_header_login_link"]) && $configs["base_header_login_link"] == "1")
                            <input type="radio" value="1" id="base_header_login_link_on" name="base_header_login_link" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_header_login_link_on" name="base_header_login_link" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_login_link_on">表示する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["base_header_login_link"]) && $configs["base_header_login_link"] == "0")
                            <input type="radio" value="0" id="base_header_login_link_off" name="base_header_login_link" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="base_header_login_link_off" name="base_header_login_link" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_login_link_off">表示しない</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">ログインリンクを表示するかどうかを選択</small>
        </div>

        {{-- 自動ユーザ登録の使用 --}}
        <div class="form-group">
            <label class="col-form-label">自動ユーザ登録の使用</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["user_register_enable"]) && $configs["user_register_enable"] == "1")
                            <input type="radio" value="1" id="user_register_enable_on" name="user_register_enable" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="user_register_enable_on" name="user_register_enable" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="user_register_enable_on">許可する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["user_register_enable"]) && $configs["user_register_enable"] == "0")
                            <input type="radio" value="0" id="user_register_enable_off" name="user_register_enable" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="user_register_enable_off" name="user_register_enable" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="user_register_enable_off">許可しない</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">自動ユーザ登録を使用するかどうかを選択</small>
        </div>

        {{-- 画像の保存機能の無効化 --}}
        <div class="form-group">
            <label class="col-form-label">画像の保存機能の無効化</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["base_mousedown_off"]) && $configs["base_mousedown_off"] == "1")
                            <input name="base_mousedown_off" value="1" type="checkbox" class="custom-control-input" id="base_mousedown_off" checked="checked">
                        @else
                            <input name="base_mousedown_off" value="1" type="checkbox" class="custom-control-input" id="base_mousedown_off">
                        @endif
                        <label class="custom-control-label" for="base_mousedown_off">ドラッグ禁止</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["base_mousedown_off"]) && $configs["base_mousedown_off"] == "1")
                            <input name="base_contextmenu_off" value="1" type="checkbox" class="custom-control-input" id="base_contextmenu_off" checked="checked">
                        @else
                            <input name="base_contextmenu_off" value="1" type="checkbox" class="custom-control-input" id="base_contextmenu_off">
                        @endif
                        <label class="custom-control-label" for="base_contextmenu_off">右クリックメニュー禁止</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["base_mousedown_off"]) && $configs["base_mousedown_off"] == "1")
                            <input name="base_touch_callout" value="1" type="checkbox" class="custom-control-input" id="base_touch_callout" checked="checked">
                        @else
                            <input name="base_touch_callout" value="1" type="checkbox" class="custom-control-input" id="base_touch_callout">
                        @endif
                        <label class="custom-control-label" for="base_touch_callout">スマホ長押し禁止</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">画像の保存機能を無効化するものを選択</small>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </form>
</div>
</div>

@endsection
