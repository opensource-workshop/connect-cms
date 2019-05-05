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

<div class="panel panel-default">
<div class="panel-body">

    <form action="/manage/site/update" method="POST">
    {{csrf_field()}}

        {{-- サイト名 --}}
        <div class="form-group">
            <label class="control-label">サイト名</label>
            <input type="text" name="base_site_name" value="{{$configs["base_site_name"]}}" class="form-control">
            <div class="help-block">サイト名（各ページで上書き可能 ※予定）</div>
        </div>

        {{-- 背景色 --}}
        <div class="form-group">
            <label class="control-label">背景色</label>
            <input type="text" name="base_background_color" value="{{$configs["base_background_color"]}}" class="form-control">
            <div class="help-block">画面の基本の背景色（各ページで上書き可能）</div>
        </div>

        {{-- ヘッダーの背景色 --}}
        <div class="form-group">
            <label class="control-label">ヘッダーの背景色</label>
            <input type="text" name="base_header_color" value="{{$configs["base_header_color"]}}" class="form-control">
            <div class="help-block">画面の基本のヘッダー背景色（各ページで上書き可能）</div>
        </div>

        {{-- ヘッダーの固定指定 --}}
        <div class="form-group">
            <label class="control-label">ヘッダーの固定</label>
            <div class="row">
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_header_fix_xs"]) && $configs["base_header_fix_xs"] == "1")
                                    <input name="base_header_fix_xs" value="1" type="checkbox" checked="checked">
                                @else
                                    <input name="base_header_fix_xs" value="1" type="checkbox">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">スマートフォン</span>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_header_fix_sm"]) && $configs["base_header_fix_sm"] == "1")
                                    <input name="base_header_fix_sm" value="1" type="checkbox" checked="checked">
                                @else
                                    <input name="base_header_fix_sm" value="1" type="checkbox">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">タブレット</span>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_header_fix_md"]) && $configs["base_header_fix_md"] == "1")
                                    <input name="base_header_fix_md" value="1" type="checkbox" checked="checked">
                                @else
                                    <input name="base_header_fix_md" value="1" type="checkbox">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">PC</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="help-block">ヘッダーを固定にするサイズをチェック</div>
        </div>

        {{-- ログインリンクの表示 --}}
        <div class="form-group">
            <label class="control-label">ログインリンクの表示</label>
            <div class="row">
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_header_login_link"]) && $configs["base_header_login_link"] == "1")
                                    <input name="base_header_login_link" type="radio" value="1" checked="checked">
                                @else
                                    <input name="base_header_login_link" type="radio" value="1">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">表示する</span>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_header_login_link"]) && $configs["base_header_login_link"] == "0")
                                    <input name="base_header_login_link" type="radio" value="0" checked="checked">
                                @else
                                    <input name="base_header_login_link" type="radio" value="0">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">表示しない</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="help-block">ログインリンクを表示するかどうかを選択</div>
        </div>

        {{-- 自動ユーザ登録の使用 --}}
        <div class="form-group">
            <label class="control-label">自動ユーザ登録の使用</label>
            <div class="row">
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["user_register_enable"]) && $configs["user_register_enable"] == "1")
                                    <input name="user_register_enable" type="radio" value="1" checked="checked">
                                @else
                                    <input name="user_register_enable" type="radio" value="1">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">許可する</span>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["user_register_enable"]) && $configs["user_register_enable"] == "0")
                                    <input name="user_register_enable" type="radio" value="0" checked="checked">
                                @else
                                    <input name="user_register_enable" type="radio" value="0">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">許可しない</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="help-block">自動ユーザ登録を使用するかどうかを選択</div>
        </div>

        {{-- 画像の保存機能の無効化 --}}
        <div class="form-group">
            <label class="control-label">画像の保存機能の無効化</label>
            <div class="row">
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_mousedown_off"]) && $configs["base_mousedown_off"] == "1")
                                    <input name="base_mousedown_off" value="1" type="checkbox" checked="checked">
                                @else
                                    <input name="base_mousedown_off" value="1" type="checkbox">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">ドラッグ禁止</span>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_contextmenu_off"]) && $configs["base_contextmenu_off"] == "1")
                                    <input name="base_contextmenu_off" value="1" type="checkbox" checked="checked">
                                @else
                                    <input name="base_contextmenu_off" value="1" type="checkbox">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">右クリックメニュー禁止</span>
                        </div>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($configs["base_touch_callout"]) && $configs["base_touch_callout"] == "1")
                                    <input name="base_touch_callout" value="1" type="checkbox" checked="checked">
                                @else
                                    <input name="base_touch_callout" value="1" type="checkbox">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">スマホ長押し禁止</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="help-block">ヘッダーを固定にするサイズをチェック</div>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal">更新</button>
        </div>
    </form>
</div>
</div>

@endsection
