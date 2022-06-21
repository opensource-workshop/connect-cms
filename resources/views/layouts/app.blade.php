{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
{{-- ページ名 --}}
<?php
    // URL から現在のURL パスを判定する。
    $current_url = url()->current();
    $base_url = url('/');
    $current_permanent_link = str_replace( $base_url, '', $current_url);
    $current_permanent_links = explode('/', $current_permanent_link);
    // print_r($current_permanent_links);
    $is_manage_page = false;
    if (!empty($current_permanent_links) && count($current_permanent_links) > 1 && $current_permanent_links[1] == 'manage') {
        $is_manage_page = true;
    }

/*
    // トップページの判定
    if (empty($current_permanent_link)) {
        $current_permanent_link = "/";
    }

    // URL パスでPage テーブル検索
    $current_page = \App\Page::where('permanent_link', '=', $current_permanent_link)->first();
*/
/*
    // ページ一覧の取得
    $class_name = "App\Page";
    $page_obj = new $class_name;
    //$menu_pages = $page_obj::orderBy('display_sequence')->get();
    $menu_pages = $page_obj::defaultOrderWithDepth();
*/
// move: app\Http\Middleware\ConnectInit.php で処理するように対応
// if (! isset($cc_configs)) {
    // seederを実行した場合、必ずconfigsにデータができ、データが無い場合は通常ありえないので、異常終了させる。
    // うっかり操作ミスは誰にでもありえるのため、エラーメッセージで対応方法を表示する。
    // ※ 新規インストール時、seederを実行しないと、なんでか Middleware の ConnectInit まで到達せず、cc_configsはセットされなかったため、ここで簡易チェックする。（実行されれば空のコレクションがセットされてエラーにならないんだけどねぇ）
    // ↓
    // 暫定対応：ページなしの場合、$cc_configsがセットされなかったため、exitしちゃだめ。（ページなし処理 ConnectController::__construct()から呼ばれる $this->checkPageNotFound() でabort() されるの、なんかあやしいかも。Middleware の ConnectInit が実行されない原因かも）
    // echo('DBテーブルのconfigsにデータが１件もありません。<code>php artisan db:seed</code> コマンドを実行して初期データを登録してください。');
    // exit;
    // ↓
    // $cc_configs = collect();
// }
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
{{--
@if(isset($configs_array['tracking_code']))
    {!!$configs_array['tracking_code']->value!!}
@endif
--}}
@if (Configs::getConfigsValue($cc_configs, 'tracking_code'))
    {!!Configs::getConfigsValue($cc_configs, 'tracking_code')!!}
@endif
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
{{--
@if(isset($configs_array['description']))
    <meta name="description" content="{{$configs_array['description']->getNobrValue()}}">
@endif
--}}
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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Fonts -->
    <link href="{{asset('fontawesome/css/all.min.css')}}" rel='stylesheet' type='text/css'>

    <!-- Scripts -->
    <script src="{{asset('js/app.js')}}"></script>
@if( App::environment(['local', 'staging']) )
    <script>Vue.config.devtools = true;</script>
@endif

    <!-- tempusdominus-bootstrap-4 -->
    <link rel="stylesheet" href="{{asset('css/tempusdominus-bootstrap-4/tempusdominus-bootstrap-4.min.css')}}" />
    {{--
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/ja.js"></script>
    --}}
    <script src="{{asset('js/moment.js/moment.min.js')}}"></script>
    <script src="{{asset('js/moment.js/locale/ja.js')}}"></script>
    {{--
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js"></script>
    --}}
    <script src="{{asset('js/tempusdominus-bootstrap-4/tempusdominus-bootstrap-4.min.js')}}"></script>

    <!-- Connect-CMS Global CSS -->
    <link href="{{ asset('css/connect.css') }}?version={{ filemtime(public_path() . "/css/connect.css") }}" rel="stylesheet">

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

    <!-- Connect-CMS Page CSS -->
@if (isset($page) && !empty($page->id))
    <link href="{{url('/')}}/file/css/{{$page->id}}.css" rel="stylesheet">
@else
    <link href="{{url('/')}}/file/css/0.css" rel="stylesheet">
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
    {{--  @if (isset($configs_array) && isset($configs_array['favicon'])) --}}
    @if (Configs::getConfigsValue($cc_configs, 'favicon'))
        <link href="{{url('/')}}/uploads/favicon/favicon.ico" rel="SHORTCUT ICON" />
    @endif

    <!-- Polyfill -->
    {{-- ※IEが公式に消えたら（2022年6月16日）消したい。 --}}
    @php
        $is_exist_plugin = false;
        if (isset($plugin_instances)) {
            foreach ($plugin_instances as $plugin_instance){
                if ($plugin_instance instanceof \App\Plugins\User\Whatsnews\WhatsnewsPlugin){
                    $is_exist_plugin = true;
                } elseif ($plugin_instance instanceof \App\Plugins\User\Blogs\BlogsPlugin) {
                    $is_exist_plugin = true;
                }
            }
        }
    @endphp
    @if ($is_exist_plugin)
        {{-- IEで発生する「Promiseは定義されていません。」エラー回避＠新着プラグインの非同期処理 --}}
        <script>window.Promise || document.write('<script src="//www.promisejs.org/polyfills/promise-7.0.4.min.js"><\/script>');</script>
    @endif
</head>
@php
// body任意クラスを抽出（カンマ設定時はランダムで１つ設定）
// $body_optional_class = null;
// if (isset($configs_array['body_optional_class'])) {
//     $classes = explode(',', $configs_array['body_optional_class']->value);
//     $body_optional_class = $classes[array_rand($classes)];
// }
$body_optional_class = Configs::getConfigsRandValue($cc_configs, 'body_optional_class');

// ヘッダーバーnavの文字色クラス
// change: 管理画面ではviewに共通的に変数をセットする仕組みがあったため、管理画面・一般画面どちらも表示するためにここで再度Configsをgetした(苦肉の策)を、共通の$cc_configsを参照するよう見直し
//$base_header_font_color_class = Configs::getConfigsValue($configs, 'base_header_font_color_class', BaseHeaderFontColorClass::navbar_dark);
// if (isset($configs) && isset($configs['base_header_font_color_class'])) {
//     $base_header_font_color_class = $configs['base_header_font_color_class'];
// } else {
//     $base_header_font_color_class = BaseHeaderFontColorClass::navbar_dark;
// }
// $config_basic_header = Configs::where('category', 'general')->get();
$base_header_font_color_class = Configs::getConfigsValue($cc_configs, 'base_header_font_color_class', BaseHeaderFontColorClass::navbar_dark);

// ヘッダーバー任意クラスを抽出（カンマ設定時はランダムで１つ設定）
// $base_header_optional_class = Configs::getConfigsValue($cc_configs, 'base_header_optional_class', null);
// $base_header_classes = explode(',', $base_header_optional_class);
// $base_header_optional_class = $base_header_classes[array_rand($base_header_classes)];
$base_header_optional_class = Configs::getConfigsRandValue($cc_configs, 'base_header_optional_class');

@endphp
<body class="@if(isset($page)){{$page->getPermanentlinkClassname()}}@endif {{ $body_optional_class }}">
{{--
@if (Auth::check() || (isset($configs) && isset($configs['base_header_hidden']) && ($configs['base_header_hidden'] != '1')))
<nav class="navbar navbar-expand-md bg-dark {{$base_header_font_color_class}} @if (isset($configs) && ($configs['base_header_fix'] == '1')) sticky-top @endif {{ $base_header_optional_class }}" aria-label="ヘッダー">
--}}
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
                    {{--
                    @if (isset($configs) &&
                            (!isset($configs['smartphone_menu_template']) ||
                                (isset($configs['smartphone_menu_template']) && ($configs['smartphone_menu_template'] == ''))
                            )
                        )
                    --}}
                    @if (Configs::getConfigsValue($cc_configs, 'smartphone_menu_template', '') == '')
                        {{-- 非表示のページは対象外 --}}
                        @if ($page_obj->isView(Auth::user(), false, true, $page_roles))

                            <li class="nav-item">
                            {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                            @if (isset($page_obj) && isset($page) && $page_obj->id == $page->id)
                                <a href="{{ $page_obj->getUrl() }}" {!!$page_obj->getUrlTargetTag()!!} class="nav-link active">
                            @else
                                <a href="{{ $page_obj->getUrl() }}" {!!$page_obj->getUrlTargetTag()!!} class="nav-link">
                            @endif

                            {{-- 各ページの深さをもとにインデントの表現 --}}
                            @for ($i = 0; $i < $page_obj->depth; $i++)
                                @if ($i+1==$page_obj->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
                            @endfor
                                {{$page_obj->page_name}}
                                </a>
                            </li>
                        @endif
                    {{-- @elseif (isset($configs) && isset($configs['smartphone_menu_template']) && ($configs['smartphone_menu_template'] == 'opencurrenttree')) --}}
                    @elseif (Configs::getConfigsValue($cc_configs, 'smartphone_menu_template') == 'opencurrenttree')

                        {{-- 非表示のページは対象外 --}}
                        @if ($page_obj->isView(Auth::user(), false, true, $page_roles))

                            {{-- カレント or 自分のルート筋 or 第1階層 or 子のページ or 同階層のページ なら表示する --}}
                            @if (isset($page) && ($page_obj->isAncestorOf($page) || $page->id == $page_obj->id || $page_obj->isChildOf($page) || $page_obj->isSiblingOf($page)) || $page_obj->depth == 0)

                                <li class="nav-item">
                                {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                                @if (isset($page_obj) && isset($page) && $page_obj->id == $page->id)
                                    <a href="{{ $page_obj->getUrl() }}" {!!$page_obj->getUrlTargetTag()!!} class="nav-link active">
                                @else
                                    <a href="{{ $page_obj->getUrl() }}" {!!$page_obj->getUrlTargetTag()!!} class="nav-link">
                                @endif

                                {{-- 各ページの深さをもとにインデントの表現 --}}
                                @for ($i = 0; $i < $page_obj->depth; $i++)
                                    @if ($i+1==$page_obj->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
                                @endfor
                                {{$page_obj->page_name}}

                                {{-- カレントもしくは自分のルート筋なら＋、違えば－を表示する --}}
                                @if (count($page_obj->children) > 0)
                                    @if (isset($page) && ($page_obj->isAncestorOf($page) || $page_obj->id == $page->id))
                                        <i class="fas fa-minus"></i>
                                    @else
                                        <i class="fas fa-plus"></i>
                                    @endif
                                @endif

                                </a>
                            </li>
                            @endif
                        @endif
                    @endif
                @endforeach
            @endif
        </ul>

        <div class="dropdown-divider d-md-none"></div>

        <ul class="navbar-nav text-nowrap">
            {{-- 管理メニュー表示判定（管理機能 or コンテンツ権限に付与がある場合）--}}
            @if (Auth::check() && Auth::user()->can('role_manage_or_post'))
                <li class="nav-item dropdown">
                    {{-- ページリストがある場合は、コンテンツ画面 --}}
                    @if (isset($page_list) && !$is_manage_page)
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown_manage" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">管理機能</a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_manage">

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
                                            <a href="{{ url()->current() }}" class="dropdown-item">プレビュー終了</a>
                                        @else
                                            <a href="{{ url()->current() }}/?mode=preview" class="dropdown-item">プレビューモード</a>
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
                                @endif
                            @endif
                        </div>
                    @else
                        <a class="nav-link" href="{{ url('/') }}">コンテンツ画面へ</a>
                    @endif
                </li>
            {{-- /管理メニュー表示判定（管理機能 or コンテンツ権限に付与がある場合）--}}
            @endif

            @guest
                {{-- @if (isset($configs['base_header_login_link']) && ($configs['base_header_login_link'] == '1')) --}}
                @if (Configs::getConfigsValue($cc_configs, 'base_header_login_link') == '1')
                    @php
                        // 外部認証設定 取得
                        $auth_method_event = Configs::getAuthMethodEvent();
                    @endphp

                    @if ($auth_method_event->value == AuthMethodType::shibboleth)
                        <li><a class="nav-link" href="{{ route('shibboleth.login') }}">ログイン</a></li>
                    @else
                        <li><a class="nav-link" href="{{ route('login') }}">ログイン</a></li>
                    @endif
                @endif
                {{-- @if (isset($configs['user_register_enable']) && ($configs['user_register_enable'] == '1')) --}}
                @if (Configs::getConfigsValue($cc_configs, 'user_register_enable') == '1')
                    <li><a class="nav-link" href="{{ route('register') }}">ユーザ登録</a></li>
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
        // $message_first_show_type = isset($configs_array['message_first_show_type']) ? $configs_array['message_first_show_type']->value : 0;
        // $message_first_permission_type = isset($configs_array['message_first_permission_type']) ? $configs_array['message_first_permission_type']->value : 0;
        // $message_first_exclued_urls = isset($configs_array['message_first_exclued_url']) ? explode(',' ,$configs_array['message_first_exclued_url']->value) : array();
        // $message_first_optional_class = isset($configs_array['message_first_optional_class']) ? $configs_array['message_first_optional_class']->value : '';
        $message_first_show_type = Configs::getConfigsValue($cc_configs, 'message_first_show_type', 0);
        $message_first_permission_type = Configs::getConfigsValue($cc_configs, 'message_first_permission_type', 0);
        $message_first_exclued_urls = explode(',', Configs::getConfigsValue($cc_configs, 'message_first_exclued_url', ''));
        $message_first_optional_class = Configs::getConfigsValue($cc_configs, 'message_first_optional_class', '');
        // dd($message_first_show_type, $message_first_permission_type, $message_first_exclued_urls, $message_first_optional_class, !in_array($page->permanent_link ,$message_first_exclued_urls), Cookie::get('connect_cookie_message_first'));
    @endphp
    {{-- 管理画面で設定ON、且つ、本ページが初回確認メッセージ表示の除外URL以外、且つ、同意していない（Cookie未セット）場合にメッセージ表示 --}}
    @if($message_first_show_type == ShowType::show && isset($page) && !in_array($page->permanent_link ,$message_first_exclued_urls) && Cookie::get('connect_cookie_message_first') != 'agreed')

        <!-- 初回確認メッセージ表示用のモーダルウィンドウ -->
        <div class="modal {{ $message_first_optional_class }}" id="first_message_modal" tabindex="-1" role="dialog" aria-labelledby="first_message_modal_label" aria-hidden="true" data-backdrop="{{ $message_first_permission_type == PermissionType::not_allowed ? 'static' : 'true' }}">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        {{-- メッセージ内容 --}}
                        {{-- $configs_array['message_first_content']->value --}}
                        {!! Configs::getConfigsValue($cc_configs, 'message_first_content') !!}
                    </div>
                    <div class="modal-footer">
                        <form action="{{url('/core/cookie/setCookieForMessageFirst')}}/{{$page->id}}" name="form_set_cookie" id="form_set_cookie" method="POST">
                            {{ csrf_field() }}

                            {{-- ボタン --}}
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="submit_form_set_cookie();">
                                {{-- $configs_array['message_first_button_name']->value --}}
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
