{{--
 * メニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/osws_add.css') }}" rel="stylesheet">

{{--
    <!-- jQuery -->
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>

    <!-- bootstrap-treeview -->
    <link href="{{ asset('css/bootstrap-treeview.css') }}" rel="stylesheet">
    <script src="{{ asset('js/bootstrap-treeview.js') }}"></script>
--}}

{{--
    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
--}}

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">

                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

{{-- ページ名 --}}
<?php

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

	// ページ一覧の取得
	$class_name = "App\Page";
	$page_obj = new $class_name;
	//$menu_pages = $page_obj::orderBy('display_sequence')->get();
	$menu_pages = $page_obj::defaultOrderWithDepth();
?>

<div class="list-group visible-xs">
@foreach($menu_pages as $page)

    {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
    <a href="{{ url("$page->permanent_link") }}" class="list-group-item">

    {{-- 各ページの深さをもとにインデントの表現 --}}
    @for ($i = 0; $i < $page->depth; $i++)
        <span @if ($i+1==$page->depth) class="glyphicon glyphicon-chevron-right" style="color: #c0c0c0;"@else style="padding-left:15px;"@endif></span>
    @endfor
    {{$page->page_name}}
    </a>

@endforeach
</div>

<div class="list-group visible-xs">

	@guest
		<a class="list-group-item" href="{{ route('login') }}">Login</a>
		<a class="list-group-item" href="{{ route('register') }}">Register</a>
	@else
		<li class="dropdown list-unstyled">
			<a href="#" class="dropdown-toggle list-group-item" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" v-pre>
				{{ Auth::user()->name }} <span class="caret"></span>
			</a>

			<ul class="dropdown-menu">
				<li>
					<a href="{{ route('logout') }}"
						onclick="event.preventDefault();
						document.getElementById('logout-form').submit();">
						Logout
					</a>

					<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
						{{ csrf_field() }}
					</form>
				</li>
			</ul>
		</li>
		<a href="{{ url('/manage/page/') }}" class="list-group-item">ページ管理</a>
	@endguest
</div>


                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right hidden-xs">
                        <!-- Authentication Links -->
                        @guest
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li><a href="{{ url('/manage/page/') }}">ページ管理</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
