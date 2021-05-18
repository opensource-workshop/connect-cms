{{--
 * サイト管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
--}}
@php
use App\Models\Core\Configs;
@endphp

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

    {{-- 共通エラーメッセージ 呼び出し --}}
    @include('common.errors_form_line')

    <form action="{{url('/')}}/manage/site/update" method="POST">
        {{csrf_field()}}

        {{-- サイト名 --}}
        <div class="form-group">
            <label class="col-form-label">サイト名</label>
            <input type="text" name="base_site_name" value="{{Configs::getConfigsValueAndOld($configs, "base_site_name")}}" class="form-control">
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
                            <option value="{{$sub_theme['dir']}}"@if(old('base_theme', $current_base_theme) == $sub_theme['dir']) selected @endif>{{$sub_theme['name']}}</option>
                        @endforeach
                        </optgroup>
                    @else
                        <option value="{{$theme['dir']}}"@if(old('base_theme', $current_base_theme) == $theme['dir']) selected @endif>{{$theme['name']}}</option>
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
                <input type="text" name="base_background_color" id="base_background_color" value="{{Configs::getConfigsValueAndOld($configs, "base_background_color")}}" class="form-control" v-model="v_base_background_color" placeholder="{{ $placeholder_message }}">
                <small class="form-text text-muted">画面の基本の背景色（各ページで上書き可能）</small>
                @if (!$is_ie)
                    {{-- IEなら表示しない --}}
                    <input type="color" v-model="v_base_background_color">
                    <small class="text-muted">左のカラーパレットから選択することも可能です。</small>
                @endif
            </div>

            {{-- ヘッダーの背景色 --}}
            <div class="form-group">
                <label class="col-form-label">ヘッダーバーの背景色</label>
                <input type="text" name="base_header_color" id="base_header_color" value="{{Configs::getConfigsValueAndOld($configs, "base_header_color")}}" class="form-control" v-model="v_base_header_color" placeholder="{{ $placeholder_message }}">
                <small class="form-text text-muted">画面の基本のヘッダー背景色（各ページで上書き可能）</small>
                @if (!$is_ie)
                    {{-- IEなら表示しない --}}
                    <input type="color" v-model="v_base_header_color">
                    <small class="text-muted">左のカラーパレットから選択することも可能です。</small>
                @endif
            </div>

            {{-- 基本のヘッダー文字色 --}}
            <div class="form-group">
                <label class="col-form-label">ヘッダーバーの文字色</label>
                <div class="row">
                    @foreach (BaseHeaderFontColorClass::getMembers() as $value => $label_name )
                        <div class="col-md-3">
                            <div class="custom-control custom-radio custom-control-inline">
                                @if(Configs::getConfigsValueAndOld($configs, "base_header_font_color_class", BaseHeaderFontColorClass::navbar_dark) == $value)
                                    <input type="radio" value="{{$value}}" id="base_header_font_color_class_{{$value}}" name="base_header_font_color_class" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="{{$value}}" id="base_header_font_color_class_{{$value}}" name="base_header_font_color_class" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="base_header_font_color_class_{{$value}}">{{$label_name}}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">ヘッダーバーの各リンクを含めた文字色を選択</small>
            </div>

            {{-- ヘッダーバー任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">ヘッダーバーの任意クラス</label>
                <input type="text" name="base_header_optional_class" id="base_header_optional_class" value="{{Configs::getConfigsValueAndOld($configs, "base_header_optional_class")}}" class="form-control">
                <small class="form-text text-muted">ヘッダーバーに任意のclass属性を設定します。カンマ区切りで複数設定した場合、いづれかのクラスをランダムで設定します。</small>
                <small class="form-text text-muted">（用例）ヘッダーバーCSSのランダム適用等</small>
            </div>

            {{-- センターエリア任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">センターエリア任意クラス</label>
                <input type="text" name="center_area_optional_class" id="center_area_optional_class" value="{{Configs::getConfigsValueAndOld($configs, "center_area_optional_class")}}" class="form-control">
                <small class="form-text text-muted">センターエリア要素に任意のclass属性を設定します。カンマ区切りで複数設定した場合、いづれかのクラスをランダムで設定します。</small>
                <small class="form-text text-muted">（用例）センターエリアCSSのランダム適用等</small>
            </div>

            {{-- body任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">bodyタグ任意クラス</label>
                <input type="text" name="body_optional_class" id="body_optional_class" value="{{Configs::getConfigsValueAndOld($configs, "body_optional_class")}}" class="form-control">
                <small class="form-text text-muted">bodyタグに任意のclass属性を設定します。カンマ区切りで複数設定した場合、いづれかのクラスをランダムで設定します。</small>
                <small class="form-text text-muted">（用例）bodyタグCSSのランダム適用等</small>
            </div>

            {{-- フッターエリア任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">フッターエリア任意クラス</label>
                <input type="text" name="footer_area_optional_class" id="footer_area_optional_class" value="{{Configs::getConfigsValueAndOld($configs, "footer_area_optional_class")}}" class="form-control">
                <small class="form-text text-muted">フッターエリア要素に任意のclass属性を設定します。カンマ区切りで複数設定した場合、いづれかのクラスをランダムで設定します。</small>
                <small class="form-text text-muted">（用例）フッターエリアCSSのランダム適用等</small>
            </div>
        </div>
        {{-- ヘッダーの表示指定 --}}
        <div class="form-group">
            <label class="col-form-label">ヘッダーバーの表示</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_header_hidden") == "0")
                            <input type="radio" value="0" id="base_header_hidden_off" name="base_header_hidden" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="base_header_hidden_off" name="base_header_hidden" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_hidden_off">表示する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_header_hidden") == "1")
                            <input type="radio" value="1" id="base_header_hidden_on" name="base_header_hidden" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_header_hidden_on" name="base_header_hidden" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_hidden_on">表示しない</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">
                未ログイン時にヘッダーバーを表示するかどうかを選択<br />
                ヘッダーバーを「表示しない」場合、ログインリンクも画面から消えます。その時はログインURL <code>{{url('/')}}/{{config('connect.LOGIN_PATH')}}</code> を直接入力してログインを行ってください。<br />
                PC時のヘッダーバー表示例）<br />
                <img class="img-fluid" src="{{url('/')}}/images/core/top_header/top_header.jpg" alt="画面最上部に表示されるヘッダー">
            </small>
        </div>

        {{-- ヘッダーの固定指定 --}}
        <div class="form-group">
            <label class="col-form-label">ヘッダーバーの固定</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_header_fix") == "0")
                            <input type="radio" value="0" id="base_header_fix_off" name="base_header_fix" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="base_header_fix_off" name="base_header_fix" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_fix_off">固定しない</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_header_fix") == "1")
                            <input type="radio" value="1" id="base_header_fix_on" name="base_header_fix" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_header_fix_on" name="base_header_fix" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_fix_on">固定する</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">
                ※ ヘッダーバーを画面上部に固定するかどうかを選択<br />
                ※ ヘッダーバーを「固定する」場合、メニューが多くなっていないかご確認ください。<br />
                スマートフォンでヘッダーバーのメニューを表示する時、スマートフォン画面の高さ以上にメニューが増えると、ヘッダーバーが固定される関係でメニューがスクロールしないため、画面外のメニューが押せなくなります。<br />
            </small>
        </div>

        {{-- ログインリンクの表示 --}}
        <div class="form-group">
            <label class="col-form-label">ログインリンクの表示</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_header_login_link") == "1")
                            <input type="radio" value="1" id="base_header_login_link_on" name="base_header_login_link" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_header_login_link_on" name="base_header_login_link" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_login_link_on">表示する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_header_login_link") == "0")
                            <input type="radio" value="0" id="base_header_login_link_off" name="base_header_login_link" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="base_header_login_link_off" name="base_header_login_link" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_header_login_link_off">表示しない</label>
                    </div>
                </div>
            </div>
            <small class="form-text text-muted">
                ログインリンクを表示するかどうかを選択<br />
                ログインリンクを「表示しない」場合、ログインURL <code>{{url('/')}}/{{config('connect.LOGIN_PATH')}}</code> を直接入力してログインを行ってください。
            </small>
        </div>

        {{-- パスワードリセット --}}
        <div class="form-group">
            <label class="col-form-label">パスワードリセットの使用</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_login_password_reset") == "1")
                            <input type="radio" value="1" id="base_login_password_reset_on" name="base_login_password_reset" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="base_login_password_reset_on" name="base_login_password_reset" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="base_login_password_reset_on">許可する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "base_login_password_reset") == "0")
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

        {{-- ログイン後に移動するページ設定 --}}
        <div class="form-group">
            <label class="col-form-label">ログイン後に移動するページ</label>
            <div class="row">
                @foreach (BaseLoginRedirectPage::getMembers() as $value => $label_name)
                    <div class="col-md-3">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio"
                                value="{{$value}}"
                                id="base_login_redirect_previous_page_{{$value}}"
                                name="base_login_redirect_previous_page"
                                class="custom-control-input"
                                @if(Configs::getConfigsValueAndOld($configs, "base_login_redirect_previous_page") == $value) checked="checked" @endif
                            >
                            <label class="custom-control-label" for="base_login_redirect_previous_page_{{$value}}">{{$label_name}}</label>
                        </div>
                    </div>
                @endforeach
            </div>
            @include('common.errors_inline', ['name' => 'base_login_redirect_previous_page'])
            <small class="form-text text-muted">「指定したページ」を選択する場合、下記の「ログイン後に移動する指定ページ」を選択してください。</small>
        </div>

        {{-- 指定ページ --}}
        <div class="form-group">
            <label class="col-form-label">ログイン後に移動する指定ページ</label>
            <select name="base_login_redirect_select_page" class="form-control">
                <option value=""></option>
                @foreach($pages_select as $page_select)
                    <option value="{{$page_select->permanent_link}}" @if(Configs::getConfigsValueAndOld($configs, "base_login_redirect_select_page") == $page_select->permanent_link) selected @endif>
                        @for ($i = 0; $i < $page_select->depth; $i++)
                        -
                        @endfor
                        {{$page_select->page_name}}
                    </option>
                @endforeach
            </select>
            @include('common.errors_inline', ['name' => 'base_login_redirect_select_page'])
        </div>

        {{-- マイページ --}}
        <div class="form-group">
            <label class="col-form-label">マイページの使用</label>
            <div class="row">
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "use_mypage") == "1")
                            <input type="radio" value="1" id="use_mypage_on" name="use_mypage" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="use_mypage_on" name="use_mypage" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="use_mypage_on">許可する</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if(Configs::getConfigsValueAndOld($configs, "use_mypage") == "0")
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
                        @if(Configs::getConfigsValueAndOld($configs, "base_mousedown_off") == "1")
                            <input name="base_mousedown_off" value="1" type="checkbox" class="custom-control-input" id="base_mousedown_off" checked="checked">
                        @else
                            <input name="base_mousedown_off" value="1" type="checkbox" class="custom-control-input" id="base_mousedown_off">
                        @endif
                        <label class="custom-control-label" for="base_mousedown_off">ドラッグ禁止</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(Configs::getConfigsValueAndOld($configs, "base_contextmenu_off") == "1")
                            <input name="base_contextmenu_off" value="1" type="checkbox" class="custom-control-input" id="base_contextmenu_off" checked="checked">
                        @else
                            <input name="base_contextmenu_off" value="1" type="checkbox" class="custom-control-input" id="base_contextmenu_off">
                        @endif
                        <label class="custom-control-label" for="base_contextmenu_off">右クリックメニュー禁止</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="custom-control custom-checkbox">
                        @if(Configs::getConfigsValueAndOld($configs, "base_touch_callout") == "1")
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
                <option value=""@if(Configs::getConfigsValueAndOld($configs, "smartphone_menu_template") == "") selected @endif>default</option>
                <option value="opencurrenttree"@if(Configs::getConfigsValueAndOld($configs, "smartphone_menu_template") == "opencurrenttree") selected @endif>opencurrenttree</option>
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
