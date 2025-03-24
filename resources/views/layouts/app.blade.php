{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
<?php
use App\Http\Controllers\Core\CookieCore;
use App\Http\Controllers\Core\UploadController;

    // URL から現在のURL パスを判定する。
    $current_url = url()->current();
    $base_url = url('/');
    $current_permanent_link = str_replace( $base_url, '', $current_url);
    $current_permanent_links = explode('/', $current_permanent_link);
    $is_manage_page = false;
    if (!empty($current_permanent_links) && count($current_permanent_links) > 1 && $current_permanent_links[1] == 'manage') {
        $is_manage_page = true;
    }

if (! isset($cc_configs)) {
    // cc_configsは app\Http\Middleware\ConnectInit.php で処理しているため、基本ここには入らない。
    // .envのAPP_KEYに"xxxx"とダブルクォートで囲むと`php artisan key:generate`しても変換されない＋APP_DEBUG=falseで、cc_configsなしでここに到達する。
    // その時に、正しいエラーログでエラー内容を追えるようにするため、cc_configsを初期化する。（初期化しないとcc_configs変数なしエラーになり本来のエラーにたどり着かない）
    $cc_configs = collect();
}
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
@if (Configs::getConfigsValue($cc_configs, 'tracking_code'))
    {!!Configs::getConfigsValue($cc_configs, 'tracking_code')!!}
@endif
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
@if (Configs::getConfigsValue($cc_configs, 'description'))
    <meta name="description" content="{{ StringUtils::getNobrValue(Configs::getConfigsValue($cc_configs, 'description')) }}">
@endif
    {{-- Page --}}
@if (isset($page))
    <meta name="_page_id" content="{{$page->id}}">
@else
    <meta name="_page_id" content="0">
@endif
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{csrf_token()}}">
    {{-- cc_configsのセット場所は、app\Http\Middleware\ConnectInit::handle(). 管理画面・一般画面全てのviewで参照できる --}}
    <title>@if(isset($page)){{$page->page_name}} | @endif{{ Configs::getConfigsValue($cc_configs, 'base_site_name', config('app.name', 'Connect-CMS')) }}</title>

    <!-- Styles -->
    <link href="{{ url('/') }}{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ url('/') }}{{ mix('/js/app.js') }}"></script>
@if( App::environment(['local', 'staging']) )
    <script>Vue.config.devtools = true;</script>
@endif

    <!-- Connect-CMS Global CSS -->
    <link href="{{ asset('css/connect.css') }}?version={{ filemtime(public_path() . "/css/connect.css") }}" rel="stylesheet">

    <!-- Connect-CMS Option Plugin's CSS -->
    @php
        $option_css_path = public_path('css/option');
        $option_csses = glob($option_css_path . '/*.css');
    @endphp
    @foreach ($option_csses as $css)
        @php $file = new SplFileInfo($css);@endphp
        <link href="{{ asset('css/option/' . $file->getFilename()) }}?version={{ $file->getMTime() }}" rel="stylesheet">
    @endforeach

    <!-- Themes CSS（基本） -->
@if (isset($themes['css']) && $themes['css'] != '' && file_exists(public_path() . "/themes/{$themes['css']}/themes.css"))
    <link href="{{url('/')}}/themes/{{$themes['css']}}/themes.css?version={{ filemtime(public_path() . "/themes/{$themes['css']}/themes.css") }}" rel="stylesheet">
@endif

    <!-- Themes JS（基本） -->
@if (isset($themes['js']) && $themes['js'] != '' && file_exists(public_path() . "/themes/{$themes['js']}/themes.js"))
    <script src="{{url('/')}}/themes/{{$themes['js']}}/themes.js?version={{ filemtime(public_path() . "/themes/{$themes['js']}/themes.js") }}"></script>
@endif

    <!-- Themes CSS（追加） -->
@if (isset($themes['additional_css']) && $themes['additional_css'] != '' && file_exists(public_path() . "/themes/{$themes['additional_css']}/themes.css"))
    <link href="{{url('/')}}/themes/{{$themes['additional_css']}}/themes.css?version={{ filemtime(public_path() . "/themes/{$themes['additional_css']}/themes.css") }}" rel="stylesheet">
@endif

    <!-- Themes JS（追加） -->
@if (isset($themes['additional_js']) && $themes['additional_js'] != '' && file_exists(public_path() . "/themes/{$themes['additional_js']}/themes.js"))
    <script src="{{url('/')}}/themes/{{$themes['additional_js']}}/themes.js?version={{ filemtime(public_path() . "/themes/{$themes['additional_js']}/themes.js") }}"></script>
@endif

    <!-- Connect-CMS Site CSS -->
    <link href="{{url('/')}}/file/css/site.css?version={{UploadController::getSiteCssTimestamp()}}" rel="stylesheet">
    <!-- Connect-CMS Page CSS -->
@if (isset($page) && !empty($page->id))
    <link href="{{url('/')}}/file/css/page/{{$page->id}}.css?version={{UploadController::getPageCssTimestamp($page->id)}}" rel="stylesheet">
@endif

    <!-- Context -->
    <script>
    {{-- @if (isset($configs) && ($configs['base_mousedown_off'] == '1')) --}}
    @if (Configs::getConfigsValue($cc_configs, 'base_mousedown_off') == '1')
        $(document).on('mousedown', 'img', function (e) { e.preventDefault(); });
    @endif
    {{-- @if (isset($configs) && ($configs['base_contextmenu_off'] == '1')) --}}
    @if (Configs::getConfigsValue($cc_configs, 'base_contextmenu_off') == '1')
        $(document).on('contextmenu', 'img', function () { return false; });
    @endif
    </script>

    <!-- Favicon -->
    @if (Configs::getConfigsValue($cc_configs, 'favicon'))
        <link href="{{url('/')}}/uploads/favicon/favicon.ico" rel="SHORTCUT ICON" />
    @endif
</head>
@php
// body任意クラスを抽出（カンマ設定時はランダムで１つ設定）
$body_optional_class = Configs::getConfigsRandValue($cc_configs, 'body_optional_class');

// ヘッダーバーnavの文字色クラス
// change: 管理画面ではviewに共通的に変数をセットする仕組みがあったため、管理画面・一般画面どちらも表示するためにここで再度Configsをgetした(苦肉の策)を、共通の$cc_configsを参照するよう見直し
$base_header_font_color_class = Configs::getConfigsValue($cc_configs, 'base_header_font_color_class', BaseHeaderFontColorClass::navbar_dark);

// ヘッダーバー任意クラスを抽出（カンマ設定時はランダムで１つ設定）
$base_header_optional_class = Configs::getConfigsRandValue($cc_configs, 'base_header_optional_class');

@endphp
<body class="@if(isset($page)){{$page->getPermanentlinkClassname()}}@endif {{ $body_optional_class }}">
@if (Auth::check() || Configs::getConfigsValue($cc_configs, 'base_header_hidden') != '1')
<nav class="navbar navbar-expand-md bg-dark {{$base_header_font_color_class}} @if (Configs::getConfigsValue($cc_configs, 'base_header_fix') == '1') sticky-top @endif {{ $base_header_optional_class }}" aria-label="ヘッダー">
    <!-- Branding Image -->
    <a class="navbar-brand cc-custom-brand" href="{{ url('/') }}">
        {{ Configs::getConfigsValue($cc_configs, 'base_site_name', config('app.name', 'Connect-CMS')) }}
    </a>

    <!-- SmartPhone Button -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="メニュー">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        {{-- メニュー類を右側にするため、空ulタグでnavbar-nav mr-autoを定義 --}}
        <ul class="navbar-nav mr-auto"></ul>

        <ul class="navbar-nav d-md-none">

            @if(isset($page_list))

                @foreach($page_list as $page_obj)

                    {{-- スマホメニューテンプレート(default) --}}
                    @if (Configs::getConfigsValue($cc_configs, 'smartphone_menu_template', SmartphoneMenuTemplateType::none) == SmartphoneMenuTemplateType::none)
                        {{-- default メニュー --}}
                        @include('layouts.default_menu')
                    @elseif (Configs::getConfigsValue($cc_configs, 'smartphone_menu_template') == SmartphoneMenuTemplateType::opencurrenttree)
                        {{-- opencurrenttree メニュー --}}
                        @include('layouts.opencurrenttree_menu')
                    @endif
                @endforeach
            @endif
        </ul>

        <div class="dropdown-divider d-md-none"></div>

        <ul class="navbar-nav text-nowrap">
            {{-- 管理メニュー表示判定（管理機能 or コンテンツ権限に付与がある場合）--}}
            @if (Auth::check() && Auth::user()->can('role_manage_or_post'))
                @if (config('connect.common_support_url'))
                    {{-- .env に個別サポート情報の設定がある場合のみ表示 --}}
                    @if (\Route::currentRouteName() == 'get_mypage' || \Route::currentRouteName() == 'post_mypage')
                        {{-- マイページのトップ（get_allで来る）もしくは、ルートでget_mypage --}}
                        {{-- マイページではサポートメニューは表示しない --}}
                    @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown_support" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">サポート情報</a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_auth">
                            @if (config('connect.individual_support_url'))
                                <a class="dropdown-item" href="{{config('connect.common_support_url')}}" target="_blank">共通サポートページ <i class="fas fa-external-link-alt"></i></a>
                            @else
                                <a class="dropdown-item" href="{{config('connect.common_support_url')}}" target="_blank">サポートページ <i class="fas fa-external-link-alt"></i></a>
                            @endif
                            @if (config('connect.individual_support_url'))
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{config('connect.individual_support_url')}}" target="_blank">個別サポートページ <i class="fas fa-external-link-alt"></i></a>
                            @endif
                            @if (config('connect.individual_support_password'))
                                <div class="px-4">ページ閲覧パスワード<br /><input type="text" class="form-control" value="{{config('connect.individual_support_password')}}"></div>
                            @endif
                        </div>
                    </li>
                    @endif
                @endif
                <li class="nav-item dropdown">
                    {{-- ページリストがある場合は、コンテンツ画面 --}}
                    @if (isset($page_list) && !$is_manage_page)
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown_manage" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">管理機能</a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_manage" data-handledropdownclose="true">

                            {{-- ページリストがある場合は、表のページとみなして「プラグイン追加」を表示 --}}
                            @if (isset($page_list) && !$is_manage_page)
                                {{-- プラグイン追加 --}}
                                {{-- 追加時の FrameController::addPlugin()で frames.create の権限チェックあるため、ここでも同様にチェックする --}}
                                @if (Auth::user()->can('frames.create'))
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#pluginAddModal">プラグイン追加</a>
                                    <div class="dropdown-divider"></div>
                                @endif

                                {{-- プレビューモード --}}
                                {{-- システム管理者、サイト管理者権限があれば、プレビューを有効にする（同権限でプラグイン設定できる。プレビューはプラグイン設定ボタンをOFFにする機能ため、同権限で制御する） --}}
                                @if (Auth::user()->can('role_arrangement'))
                                    @if (isset($page_list))
                                        @if (app('request')->input('mode') == 'preview')
                                            @isset ($page)
                                                <a href="{{ url($page->permanent_link) }}" class="dropdown-item">プレビュー終了</a>
                                            @else
                                                <a href="{{ url()->current() }}" class="dropdown-item">プレビュー終了</a>
                                            @endisset
                                        @else
                                            @isset ($page)
                                                <a href="{{ url($page->permanent_link) }}?mode=preview" class="dropdown-item">プレビューモード</a>
                                            @else
                                                <a href="{{ url()->current() }}/?mode=preview" class="dropdown-item">プレビューモード</a>
                                            @endisset
                                        @endif
                                        @if (Auth::user()->can('role_manage_on') && isset($page_list))
                                            <div class="dropdown-divider"></div>
                                        @endif
                                    @endif
                                @endif
                                {{-- 管理プラグインのメニュー --}}
                                @if (Auth::user()->can('role_manage_on'))
                                    @if (isset($plugin_name) && $plugin_name == 'page')
                                        <a href="{{ url('/manage') }}" class="dropdown-item active" style="border-radius: 0;">管理者メニュー</a>
                                    @else
                                        <a href="{{ url('/manage') }}" class="dropdown-item">管理者メニュー</a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                @endif

                                {{-- エリア枠付け --}}
                                @if (Auth::user()->can('frames.create'))
                                    <div class="custom-control custom-switch keep-open-on-click">
                                        <input type="checkbox" class="custom-control-input position-static keep-open-on-click" id="switch-area-border">
                                        <label class="custom-control-label keep-open-on-click" for="switch-area-border">
                                            エリア枠
                                        </label>
                                    </div>
                                    <script>
                                        // エリアの枠線を付ける
                                        $('#switch-area-border').on('click', function (event) {
                                            $('#ccHeaderArea').toggleClass('with-border');
                                            $('#ccFooterArea').toggleClass('with-border');
                                            $('#ccMainArea').toggleClass('with-border');
                                            $('#ccRightArea').toggleClass('with-border');
                                            $('#ccLeftArea').toggleClass('with-border');
                                        });

                                        // エリア枠スイッチを押したときにドロップダウンメニューを閉じないようにする
                                        $('.dropdown-menu[data-handledropdownclose="true"]').on("click.bs.dropdown", function (e) {
                                            if ($(this).parent().hasClass("show")) {
                                                var target = $(e.target);

                                                if (target.hasClass("keep-open-on-click")) {
                                                    e.stopPropagation();
                                                }
                                            }
                                        });
                                    </script>
                                @endif
                            @endif
                        </div>
                    @else
                        <a class="nav-link" href="{{ url('/') }}">トップページへ</a>
                    @endif
                </li>
            {{-- /管理メニュー表示判定（管理機能 or コンテンツ権限に付与がある場合）--}}
            @else
                {{-- マイページのトップページへ対応（管理機能 or コンテンツ権限 なしもあり） --}}
                <li class="nav-item dropdown">
                    {{-- ページリストがある場合は、コンテンツ画面 --}}
                    @if (isset($page_list) && !$is_manage_page)
                    @else
                        <a class="nav-link" href="{{ url('/') }}">トップページへ</a>
                    @endif
                </li>
            @endif

            @guest
                @if (Configs::getConfigsValue($cc_configs, 'base_header_login_link') == '1')
                    @php
                        // 外部認証設定 取得
                        $auth_method_event = Configs::getAuthMethodEvent();
                    @endphp

                    @if ($auth_method_event->value == AuthMethodType::shibboleth)
                        <li><a class="nav-link" href="{{ route('shibboleth.login') }}">{{config('connect.LOGIN_STR')}}</a></li>
                    @else
                        <li><a class="nav-link" href="{{ route('show_login_form') }}">{{config('connect.LOGIN_STR')}}</a></li>
                    @endif
                @endif
                @php
                    $user_register_enables = $cc_configs->where('category', 'user_register')
                        ->where('name', 'user_register_enable')
                        ->where('value', '1');
                @endphp
                @if ($user_register_enables->isNotEmpty())
                    <li><a class="nav-link" href="{{ route('show_register_form') }}">ユーザ登録</a></li>
                @endif
            @else
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown_auth" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{Auth::user()->name}}</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_auth">
                        @if (\Route::currentRouteName() == 'get_mypage' || \Route::currentRouteName() == 'post_mypage')
                            {{-- マイページのトップ（get_allで来る）もしくは、ルートでget_mypage --}}
                        @else
                            @if (Configs::getConfigsValue($cc_configs, 'use_mypage') == '1')
                                <a class="dropdown-item" href="{{url('/mypage')}}">マイページ</a>
                                <div class="dropdown-divider"></div>
                            @endif
                        @endif
                        <a class="dropdown-item" href="{{route('logout')}}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>
            @endguest
        </ul>

    </div>
</nav>
@endif

@yield('content')

{{-- -------------------------------------------- --}}

    {{-- プラグイン追加・ダイアログ --}}
    @auth
    @if (isset($page) && isset($layouts_info))
    <div class="modal fade" id="pluginAddModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">プラグイン追加</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">

                    <div class="d-sm-flex justify-content-around">
                        @if ($layouts_info[0]['exists'] == 1)
                            @include('layouts.add_plugin',['area_name' => 'ヘッダー', 'area_id' => 0])
                        @else
                            <span class="form-control" style="background-color: #f0f0f0;">ヘッダーなし</span>
                        @endif
                    </div>

                    <div class="d-sm-flex justify-content-around">
                        <div class="m-3">
                            @if ($layouts_info[1]['exists'] == 1)
                                @include('layouts.add_plugin',['area_name' => '左', 'area_id' => 1])
                            @else
                                <span class="form-control" style="background-color: #f0f0f0;">左カラムなし</span>
                            @endif
                        </div>
                        <div class="m-3">
                            @include('layouts.add_plugin',['area_name' => 'メイン', 'area_id' => 2])
                        </div>
                        <div class="m-3">
                            @if ($layouts_info[3]['exists'] == 1)
                                @include('layouts.add_plugin',['area_name' => '右', 'area_id' => 3])
                            @else
                            <span class="form-control" style="background-color: #f0f0f0;">右カラムなし</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-sm-flex justify-content-around">
                        @if ($layouts_info[4]['exists'] == 1)
                            @include('layouts.add_plugin',['area_name' => 'フッター', 'area_id' => 4])
                        @else
                            <span class="form-control" style="background-color: #f0f0f0;">フッターなし</span>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> 閉じる</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endauth

    {{-- 初回確認メッセージの表示モーダル --}}
    @php
        // Configsテーブルから設定値を取得
        $message_first_show_type = Configs::getConfigsValue($cc_configs, 'message_first_show_type', 0);
        $message_first_permission_type = Configs::getConfigsValue($cc_configs, 'message_first_permission_type', 0);
        $message_first_exclued_urls = explode(',', Configs::getConfigsValue($cc_configs, 'message_first_exclued_url', ''));
        $message_first_optional_class = Configs::getConfigsValue($cc_configs, 'message_first_optional_class', '');
    @endphp
    {{-- 管理画面で設定ON、且つ、本ページが初回確認メッセージ表示の除外URL以外、且つ、（Cookie未セット or Cookieありで初回メッセージの更新日より古い）場合にメッセージ表示 --}}
    @if ($message_first_show_type == ShowType::show && isset($page) && !in_array($page->permanent_link ,$message_first_exclued_urls) && (!Cookie::has('connect_cookie_message_first') || Cookie::get('connect_cookie_message_first') < CookieCore::getCookieForMessageTimestamp()))
        <!-- 初回確認メッセージ表示用のモーダルウィンドウ -->
        <div class="modal {{ $message_first_optional_class }}" id="first_message_modal" tabindex="-1" role="dialog" aria-labelledby="first_message_modal_label" aria-hidden="true" data-backdrop="{{ $message_first_permission_type == PermissionType::not_allowed ? 'static' : 'true' }}">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        {{-- メッセージ内容 --}}
                        {!! Configs::getConfigsValue($cc_configs, 'message_first_content') !!}
                    </div>
                    <div class="modal-footer">
                        <form action="{{url('/core/cookie/setCookieForMessageFirst')}}/{{$page->id}}" name="form_set_cookie" id="form_set_cookie" method="POST">
                            {{ csrf_field() }}

                            {{-- ボタン --}}
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="submit_form_set_cookie();">
                                {{ Configs::getConfigsValue($cc_configs, 'message_first_button_name') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // 画面ロード時にモーダル表示
            window.onload = function() {
                $('#first_message_modal').modal();
            };
            // cookieセット処理リクエスト
            function submit_form_set_cookie() {
                form_set_cookie.submit();
            }
        </script>
    @endif

</body>
</html>
