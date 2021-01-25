{{--
 * サイト管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.site.site_manage_tab')

</div>
<div class="card-body">

    <form action="{{url('/')}}/manage/site/update" method="POST">
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
            <select name="base_theme" class="form-control">
                <option value="">テーマなし</option>
                @foreach($themes as $theme)
                    @isset($theme['themes'])
                        <optgroup label="{{$theme['name']}}">
                        @foreach($theme['themes'] as $sub_theme)
                            <option value="{{$sub_theme['dir']}}"@if($sub_theme['dir'] == $current_base_theme) selected @endif>{{$sub_theme['name']}}</option>
                        @endforeach
                        </optgroup>
                    @else
                        <option value="{{$theme['dir']}}"@if($theme['dir'] == $current_base_theme) selected @endif>{{$theme['name']}}</option>
                    @endisset
                @endforeach
            </select>
        </div>

        <div id="app">
            @php
                // IEか判定
                $ua = $_SERVER['HTTP_USER_AGENT'];
                $is_ie = false;
                $placeholder_message = 'HTMLカラーコードを入力';
                if (strstr($ua, 'Trident') || strstr($ua, 'MSIE')) {
                    $is_ie = true;
                }
            @endphp
            {{-- 背景色 --}}
            <div class="form-group">
                <label class="col-form-label">背景色</label>
                <input type="text" name="base_background_color" id="base_background_color" value="{{$configs["base_background_color"]}}" class="form-control" v-model="v_base_background_color" placeholder="{{ $placeholder_message }}">
                <small class="form-text text-muted">画面の基本の背景色（各ページで上書き可能）</small>
                @if (!$is_ie)
                    {{-- IEなら表示しない --}}
                    <input type="color" v-model="v_base_background_color">
                    <small class="text-muted">左のカラーパレットから選択することも可能です。</small>
                @endif
            </div>

            {{-- ヘッダーの背景色 --}}
            <div class="form-group">
                <label class="col-form-label">ヘッダーの背景色</label>
                <input type="text" name="base_header_color" id="base_header_color" value="{{$configs["base_header_color"]}}" class="form-control" v-model="v_base_header_color" placeholder="{{ $placeholder_message }}">
                <small class="form-text text-muted">画面の基本のヘッダー背景色（各ページで上書き可能）</small>
                @if (!$is_ie)
                    {{-- IEなら表示しない --}}
                    <input type="color" v-model="v_base_header_color">
                    <small class="text-muted">左のカラーパレットから選択することも可能です。</small>
                @endif
            </div>

            {{-- センターエリア任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">センターエリア任意クラス</label>
                <input type="text" name="center_area_optional_class" id="center_area_optional_class" value="{{$configs["center_area_optional_class"]}}" class="form-control">
                <small class="form-text text-muted">センターエリア要素に任意のclass属性を設定します。カンマ区切りで複数設定した場合、いづれかのクラスをランダムで設定します。</small>
                <small class="form-text text-muted">（用例）センターエリアCSSのランダム適用等</small>
            </div>

            {{-- body任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">bodyタグ任意クラス</label>
                <input type="text" name="body_optional_class" id="body_optional_class" value="{{$configs["body_optional_class"]}}" class="form-control">
                <small class="form-text text-muted">bodyタグに任意のclass属性を設定します。カンマ区切りで複数設定した場合、いづれかのクラスをランダムで設定します。</small>
                <small class="form-text text-muted">（用例）bodyタグCSSのランダム適用等</small>
            </div>

            {{-- フッターエリア任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">フッターエリア任意クラス</label>
                <input type="text" name="footer_area_optional_class" id="footer_area_optional_class" value="{{$configs["footer_area_optional_class"]}}" class="form-control">
                <small class="form-text text-muted">フッターエリア要素に任意のclass属性を設定します。カンマ区切りで複数設定した場合、いづれかのクラスをランダムで設定します。</small>
                <small class="form-text text-muted">（用例）フッターエリアCSSのランダム適用等</small>
            </div>
        </div>
        {{-- ヘッダーの表示指定 --}}
        <div class="form-group">
            <label class="col-form-label">ヘッダーの表示</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["base_header_hidden"]) && $configs["base_header_hidden"] == "0")
                            <input type="radio" value="0" id="base_header_hidden_off" name="base_header_hidden" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="base_header_hidden_off" name="base_header_hidden" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_hidden_off">表示する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["base_header_hidden"]) && $configs["base_header_hidden"] == "1")
                            <input type="radio" value="1" id="base_header_hidden_on" name="base_header_hidden" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_header_hidden_on" name="base_header_hidden" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_hidden_on">表示しない</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">未ログイン時にヘッダーを表示するかどうかを選択</small>
        </div>

        {{-- ヘッダーの固定指定 --}}
        <div class="form-group">
            <label class="col-form-label">ヘッダーの固定</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["base_header_fix"]) && $configs["base_header_fix"] == "0")
                            <input type="radio" value="0" id="base_header_fix_off" name="base_header_fix" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="base_header_fix_off" name="base_header_fix" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_fix_off">固定しない</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["base_header_fix"]) && $configs["base_header_fix"] == "1")
                            <input type="radio" value="1" id="base_header_fix_on" name="base_header_fix" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_header_fix_on" name="base_header_fix" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_fix_on">固定する</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">ヘッダーを画面上部に固定するかどうかを選択</small>
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

        {{-- パスワードリセット --}}
        <div class="form-group">
            <label class="col-form-label">パスワードリセットの使用</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["base_login_password_reset"]) && $configs["base_login_password_reset"] == "1")
                            <input type="radio" value="1" id="base_login_password_reset_on" name="base_login_password_reset" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_login_password_reset_on" name="base_login_password_reset" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_login_password_reset_on">許可する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if((isset($configs["base_login_password_reset"]) && $configs["base_login_password_reset"] == "0"))
                            <input type="radio" value="0" id="base_login_password_reset_off" name="base_login_password_reset" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="base_login_password_reset_off" name="base_login_password_reset" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_login_password_reset_off">許可しない</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">パスワードを忘れた場合に、ユーザ自身がリセットリンクをメール送信する機能を使用するかどうかを選択</small>
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

        {{-- 自動ユーザ登録時に個人情報保護方針への同意を求めるか --}}
        <div class="form-group">
            <label class="col-form-label">個人情報保護方針への同意</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["user_register_requre_privacy"]) && $configs["user_register_requre_privacy"] == "1")
                            <input name="user_register_requre_privacy" value="1" type="checkbox" class="custom-control-input" id="user_register_requre_privacy" checked="checked">
                        @else
                            <input name="user_register_requre_privacy" value="1" type="checkbox" class="custom-control-input" id="user_register_requre_privacy">
                        @endif
                        <label class="custom-control-label" for="user_register_requre_privacy">同意を求める</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">自動ユーザ登録時に個人情報保護方針への同意を求めるか設定</small>
        </div>

        {{-- 自動ユーザ登録時に求める個人情報保護方針の表示内容 --}}
        <div class="form-group">
            <label class="col-form-label">個人情報保護方針の表示内容</label>
            <textarea name="user_register_privacy_description" class="form-control" rows=3>{!!old('user_register_privacy_description', $configs["user_register_privacy_description"])!!}</textarea>
            <small class="form-text text-muted">自動ユーザ登録時に求める個人情報保護方針への説明文</small>
        </div>

        {{-- 自動ユーザ登録時に求めるユーザ登録についての文言 --}}
        <div class="form-group">
            <label class="col-form-label">ユーザ登録について</label>
            <textarea name="user_register_description" class="form-control" rows=3>{!!old('user_register_description', $configs["user_register_description"])!!}</textarea>
            <small class="form-text text-muted">自動ユーザ登録時に求めるユーザ登録についての説明文</small>
        </div>

        {{-- マイページ --}}
        <div class="form-group">
            <label class="col-form-label">マイページの使用</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(isset($configs["use_mypage"]) && $configs["use_mypage"] == "1")
                            <input type="radio" value="1" id="use_mypage_on" name="use_mypage" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="use_mypage_on" name="use_mypage" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="use_mypage_on">許可する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if((isset($configs["use_mypage"]) && $configs["use_mypage"] == "0"))
                            <input type="radio" value="0" id="use_mypage_off" name="use_mypage" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="use_mypage_off" name="use_mypage" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="use_mypage_off">許可しない</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">ユーザ自身でパスワード変更等できるマイページ機能を使用するかどうかを選択</small>
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
                        @if(isset($configs["base_contextmenu_off"]) && $configs["base_contextmenu_off"] == "1")
                            <input name="base_contextmenu_off" value="1" type="checkbox" class="custom-control-input" id="base_contextmenu_off" checked="checked">
                        @else
                            <input name="base_contextmenu_off" value="1" type="checkbox" class="custom-control-input" id="base_contextmenu_off">
                        @endif
                        <label class="custom-control-label" for="base_contextmenu_off">右クリックメニュー禁止</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(isset($configs["base_touch_callout"]) && $configs["base_touch_callout"] == "1")
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

        {{-- スマホメニューのフォーマット --}}
        <div class="form-group">
            <label class="col-form-label">スマホメニューのフォーマット</label>
            <select name="smartphone_menu_template" class="form-control">
                <option value=""@if(!isset($configs["smartphone_menu_template"]) || $configs["smartphone_menu_template"] == "") selected @endif>default</option>
                <option value="opencurrenttree"@if(isset($configs["smartphone_menu_template"]) && $configs["smartphone_menu_template"] == "opencurrenttree") selected @endif>opencurrenttree</option>
            </select>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </form>
</div>
</div>
<script>
    new Vue({
        el: "#app",
        data: {
            v_base_background_color: document.getElementById('base_background_color').value,
            v_base_header_color: document.getElementById('base_header_color').value
        },
    })
</script>

@endsection
