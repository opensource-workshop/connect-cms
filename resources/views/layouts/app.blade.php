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
/*
    // URL から現在のURL パスを判定する。
    $current_url = url()->current();
    $base_url = url('/');
    $current_permanent_link = str_replace( $base_url, '', $current_url);

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

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{csrf_token()}}">

    @if(isset($configs))
        <title>{{$configs['base_site_name']}}</title>
    @else
        <title>{{config('app.name', 'Connect-CMS')}}</title>
    @endif

    <!-- Styles -->
{{-- bootstrap3
    <link href="{{asset('css/app.css')}}" rel="stylesheet">
--}}
{{-- bootstrap4 --}}
    <link href="{{asset('bootstrap4/css/bootstrap.min.css')}}" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/connect.css') }}" rel="stylesheet">
    @if (isset($page))
        <link href="/file/css/{{$page->id}}.css" rel="stylesheet">
    @endif

    <!-- Themes Styles -->
    @if (isset($themes))
        <link href="/themes/{{$themes}}/themes.css" rel="stylesheet">
    @endif

    <!-- jQuery -->
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>

{{-- bootstrap4 --}}
    <script src="{{asset('bootstrap4/js/bootstrap.bundle.min.js')}}"></script>

    <!-- bootstrap-datepicker -->
    <link href="{{asset('js/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet">
{{--    <script src="{{ asset('js/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script> --}}
    <script src="{{ asset('js/bootstrap-datepicker/js/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datepicker/locales/bootstrap-datepicker.ja.min.js') }}"></script>

    <!-- Context -->
    <script>
    @if (isset($configs) && ($configs['base_mousedown_off'] == '1'))
        $(document).on('mousedown', 'img', function (e) { e.preventDefault(); });
    @endif
    @if (isset($configs) && ($configs['base_contextmenu_off'] == '1'))
        $(document).on('contextmenu', 'img', function () { return false; });
    @endif
    </script>

{{--
    <!-- bootstrap-treeview -->
    <link href="{{ asset('css/bootstrap-treeview.css') }}" rel="stylesheet">
    <script src="{{ asset('js/bootstrap-treeview.js') }}"></script>
--}}

    <!-- Fonts -->
{{--
    <link href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" rel='stylesheet' type='text/css'>
--}}
    <link href="{{asset('fontawesome/css/all.min.css')}}" rel='stylesheet' type='text/css'>

{{--
    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
--}}

</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark bg-dark">
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

{{--
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Dropdown</a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                </div>
            </li>
--}}
        </ul>
{{--
        <form class="form-inline my-2 my-lg-0">
            <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
--}}

        <ul class="navbar-nav">

            {{-- システム管理者, サイト管理者, ユーザ管理者, ページ管理者, 運用管理者 の場合に、管理メニューを表示 --}}
            @if (Auth::check() && 
                (
                    Auth::user()->can(Config::get('cc_role.ROLE_SYSTEM_MANAGER')) ||
                    Auth::user()->can(Config::get('cc_role.ROLE_SITE_MANAGER')) ||
                    Auth::user()->can(Config::get('cc_role.ROLE_USER_MANAGER')) ||
                    Auth::user()->can(Config::get('cc_role.ROLE_PAGE_MANAGER')) ||
                    Auth::user()->can(Config::get('cc_role.ROLE_OPERATION_MANAGER'))
                )
            )
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown_manage" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">管理機能</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_manage">

                        {{-- ページリストがある場合は、表のページとみなして「プラグイン追加」を表示 --}}
                        @if (isset($page_list))
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#pluginAddModal">プラグイン追加</a>
                            <div class="dropdown-divider"></div>

                        @endif
                        {{-- 管理プラグインのメニュー --}}
                        @if (isset($plugin_name) && $plugin_name == 'page')
                            <a href="{{ url('/manage/page') }}" class="dropdown-item active" style="border-radius: 0;">ページ管理</a>
                        @else
                            <a href="{{ url('/manage/page') }}" class="dropdown-item">ページ管理</a>
                        @endif
                        @if (isset($plugin_name) && $plugin_name == 'site')
                            <a href="{{ url('/manage/site') }}" class="dropdown-item active" style="border-radius: 0;">サイト管理</a>
                        @else
                            <a href="{{ url('/manage/site') }}" class="dropdown-item">サイト管理</a>
                        @endif
                        @if (isset($plugin_name) && $plugin_name == 'user')
                            <a href="{{ url('/manage/user') }}" class="dropdown-item active" style="border-radius: 0;">ユーザ管理</a>
                        @else
                            <a href="{{ url('/manage/user') }}" class="dropdown-item">ユーザ管理</a>
                        @endif

                    </div>
                </li>
            {{-- /システム管理者, サイト管理者, ユーザ管理者, ページ管理者, 運用管理者 の場合に、管理メニューを表示 --}}
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endauth

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
