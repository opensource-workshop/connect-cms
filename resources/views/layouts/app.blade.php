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
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
@if(isset($configs_array['description']))
    <meta name="description" content="{{$configs_array['description']->getNobrValue()}}">
@endif
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{csrf_token()}}">
@if(isset($configs))
    <title>@if(isset($page)){{$page->page_name}} | @endif{{$configs['base_site_name']}}</title>
@else
    <title>@if(isset($page)){{$page->page_name}} | @endif{{config('app.name', 'Connect-CMS')}}</title>
@endif

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link href="{{ asset('css/connect.css') }}" rel="stylesheet">
@if (isset($page))
    <link href="/file/css/{{$page->id}}.css" rel="stylesheet">
@endif

    <!-- Themes CSS -->
@if (isset($themes['css']) && $themes['css'] != '')
    <link href="/themes/{{$themes['css']}}/themes.css" rel="stylesheet">
@endif

    <!-- Fonts -->
    <link href="{{asset('fontawesome/css/all.min.css')}}" rel='stylesheet' type='text/css'>

    <!-- Scripts -->
    <script src="{{asset('js/app.js')}}"></script>

    <!-- tempusdominus-bootstrap-4 -->
    <link rel="stylesheet" href="/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css" />
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

    <!-- Themes JS -->
@if (isset($themes['js']) && $themes['js'] != '')
    <script src="/themes/{{$themes['js']}}/themes.js"></script>
@endif

    <!-- Context -->
    <script>
    @if (isset($configs) && ($configs['base_mousedown_off'] == '1'))
        $(document).on('mousedown', 'img', function (e) { e.preventDefault(); });
    @endif
    @if (isset($configs) && ($configs['base_contextmenu_off'] == '1'))
        $(document).on('contextmenu', 'img', function () { return false; });
    @endif
    </script>

</head>
<body class="@if(isset($page)){{$page->getPermanentlinkClassname()}}@endif">

@if (Auth::check() || (isset($configs) && ($configs['base_header_hidden'] != '1')))
<nav class="navbar navbar-expand-md navbar-dark bg-dark @if (isset($configs) && ($configs['base_header_fix'] == '1')) sticky-top @endif">
    <!-- Branding Image -->
    <a class="navbar-brand" href="{{ url('/') }}">
        @if(isset($configs))
            {{$configs['base_site_name']}}
        @else
            {{ config('app.name', 'Connect-CMS') }}
        @endif
    </a>

    <!-- SmartPhone Button -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">

            @if(isset($page_list))
            <div class="d-block d-md-none">
                @foreach($page_list as $page_obj)

                    {{-- 非表示のページは対象外 --}}
                    @if ($page_obj->display_flag == 1)

                        <li class="nav-item">
                        {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                        @if (isset($page_obj) && $page_obj->id == $page->id)
                            <a href="{{ url("$page_obj->permanent_link") }}" class="nav-link active">
                        @else
                            <a href="{{ url("$page_obj->permanent_link") }}" class="nav-link">
                        @endif

                        {{-- 各ページの深さをもとにインデントの表現 --}}
                        @for ($i = 0; $i < $page_obj->depth; $i++)
                            @if ($i+1==$page_obj->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
                        @endfor
                            {{$page_obj->page_name}}
                            </a>
                        </li>
                    @endif

                @endforeach
                <div class="dropdown-divider"></div>
            </div>
            @endif
        </ul>

        <ul class="navbar-nav">
            {{-- 管理メニュー表示判定（管理機能 or 記事関連の権限に付与がある場合）--}}
            @if (Auth::check() && Auth::user()->can('role_manage_or_post'))
                <li class="nav-item dropdown">
                    {{-- ページリストがある場合は、コンテンツ画面 --}}
                    @if (isset($page_list) && !$is_manage_page)
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown_manage" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">管理機能</a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_manage">

                            {{-- ページリストがある場合は、表のページとみなして「プラグイン追加」を表示 --}}
                            @if (isset($page_list) && !$is_manage_page)
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#pluginAddModal">プラグイン追加</a>
                                <div class="dropdown-divider"></div>

                                {{-- プレビューモード --}}
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
            {{-- /管理メニュー表示判定（管理機能 or 記事関連の権限に付与がある場合）--}}
            @endif

            @guest
                @if (isset($configs) && ($configs['base_header_login_link'] == '1'))
                    <a class="nav-link" href="{{ route('login') }}">ログイン</a>
                @endif
                @if (isset($configs) && ($configs['user_register_enable'] == '1'))
                    <a class="nav-link" href="{{ route('register') }}">ユーザ登録</a>
                @endif
            @else
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown_auth" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{Auth::user()->name}}</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_auth">
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

<main role="main">
    @yield('content')
</main>

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
                            <span class="form-control" style="background-color: #f0f0f0;">左カラムなし</span>
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
                                @include('layouts.add_plugin',['area_name' => '右', 'area_id' => 3, 'disabled' => true])
                            @endif
                        </div>
                    </div>

                    <div class="d-sm-flex justify-content-around">
                        @if ($layouts_info[4]['exists'] == 1)
                            @include('layouts.add_plugin',['area_name' => 'フッター', 'area_id' => 4])
                        @else
                            <span class="form-control" style="background-color: #f0f0f0;">左カラムなし</span>
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

</body>
</html>
