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
    <link href="{{asset('css/app.css')}}" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/connect.css') }}" rel="stylesheet">
    @if (isset($current_page))
        <link href="/file/css/{{$current_page->id}}.css" rel="stylesheet">
    @endif

    <!-- jQuery -->
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}"></script>

{{--
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
                        @if(isset($configs))
                            {{$configs['base_site_name']}}
                        @else
                            {{ config('app.name', 'Connect-CMS') }}
                        @endif
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">

                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    @if(isset($page_list))
                    <div class="list-group visible-xs">
                    @foreach($page_list as $page)

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
                    @endif

                    {{-- スマートフォン用メニュー --}}
                    <div class="list-group visible-xs">

                        @guest
                            <a class="list-group-item" href="{{ route('login') }}">ログイン</a>
                            <a class="list-group-item" href="{{ route('register') }}">ユーザ登録</a>
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
                                            ログアウト
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            <a href="{{ url('/manage/page/') }}" class="list-group-item">管理ページ</a>
                        @endguest
                    </div>


                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right hidden-xs">
                        <!-- Authentication Links -->
                        @guest
                            <li><a href="{{ route('login') }}">ログイン</a></li>
                            <li><a href="{{ route('register') }}">ユーザ登録</a></li>
                        @else
                            {{-- <li><a href="{{ url('/manage/page/') }}">管理ページ</a></li> --}}
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" v-pre>
                                    管理機能 <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">
{{--
                                    @if (isset($current_page))
                                        <li><a href="{{ url('/manage/pluginadd') }}/index/{{$current_page->id}}">プラグイン追加</a></li>
                                    @else
                                        <li><a href="{{ url('/manage/pluginadd') }}">プラグイン追加</a></li>
                                    @endif
--}}
                                    <li><a href="#" data-toggle="modal" data-target="#sampleModal">プラグイン追加</a></li>
                                    <li role="separator" class="divider" style="margin: 4px 0 10px 0;"></li>
                                    <li><a href="{{ url('/manage/page') }}">ページ管理</a></li>
                                    <li><a href="{{ url('/manage/site') }}">サイト管理</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            ログアウト
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

    {{-- プラグイン追加・ダイアログ --}}
    @auth
    @if (isset($current_page))
    <div class="modal fade" id="sampleModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                    <h4 class="modal-title">プラグイン追加</h4>
                </div>
                <div class="modal-body">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-4 col-md-offset-4">
                                    @include('layouts.add_plugin',['area_name' => 'ヘッダー', 'area_no' => 1])
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <tr>
                                <td>
                                    @include('layouts.add_plugin',['area_name' => '左', 'area_no' => 2])
                                </td>
                                <td>
                                    @include('layouts.add_plugin',['area_name' => '主', 'area_no' => 3])
                                </td>
                                <td>
                                    @include('layouts.add_plugin',['area_name' => '右', 'area_no' => 4])
                                </td>
                            </tr>
                        </table>
                        <div class="panel-footer" style="background-color: #ffffff;">
                            <div class="row">
                                <div class="col-md-4 col-md-offset-4">
                                    @include('layouts.add_plugin',['area_name' => 'フッター', 'area_no' => 5])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
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
