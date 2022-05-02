<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="{{$base_path}}css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="{{$base_path}}css/manual.css" type="text/css">
    <link rel="stylesheet" href="{{$base_path}}font/css/all.min.css" type="text/css">
    <script src="{{$base_path}}js/jquery-3.6.0.min.js"></script>
    <script src="{{$base_path}}js/popper.min.js"></script>
    <script src="{{$base_path}}js/bootstrap.min.js"></script>
    <title>Connect-CMSマニュアル</title>
</head>
<body class=" ">
    <nav class="navbar navbar-expand-md bg-dark navbar-dark  " aria-label="ヘッダー">

        <!-- Branding Image -->
        <a class="navbar-brand" href="{{$base_path}}index.html">
            Connect-CMSマニュアル
        </a>

        {{-- バッジ・メニュー --}}
        <div class="d-none d-md-block">
            @include('manual.common.badge_menu')
        </div>

        <!-- SmartPhone Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="メニュー">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto"></ul>
            @if (isset($current_method))
                <ul class="navbar-nav d-md-none">
                    <li class="nav-item smp-nav-link">
                        {{-- カテゴリのリンクは常に表示 --}}
                        @if ($base_path == '../' || $base_path == '../../' || $base_path == '../../../')
                            @if ($base_path == '../')
                                <span class="smp-nav-nolink">{{ManualCategory::getDescription($current_method->category)}}</span>
                            @else
                                <a href="{{$base_path}}{{$current_method->category}}/index.html">
                                    {{ManualCategory::getDescription($current_method->category)}}
                                </a>
                            @endif
                        @endif
                        {{-- プラグインのリンクはプラグイン、メソッドで表示 --}}
                        @if ($base_path == '../../' || $base_path == '../../../')
                            &gt;
                            @if ($base_path == '../../')
                                <span class="smp-nav-nolink">{{$current_method->plugin_title}}</span>
                            @else
                                <a href="{{$base_path}}{{$current_method->category}}/{{$current_method->plugin_name}}/index.html">
                                    {{$current_method->plugin_title}}
                                </a>
                            @endif
                        @endif
                        {{-- メソッドの表示はメソッドでのみ表示 --}}
                        @if ($base_path == '../../../')
                            &gt;
                            <span class="smp-nav-nolink">{{$current_method->method_title}}</span>
                        @endif
                    </li>
                </ul>

                {{-- プラグインのリストはカテゴリの場合に表示 --}}
                @if ($base_path == '../')
                <ul class="d-md-none">
                    @foreach ($methods->where('category', $current_method->category)->groupBy('plugin_name') as $method_group)
                        @if ($method_group[0]->id == $current_method->id && $base_path == '../../../')
                            <li class="smp-nav-nolink">
                                {{$method_group[0]->plugin_title}}
                            </li>
                        @else
                            <li class="smp-nav-link">
                                <a href="{{$base_path}}{{$method_group[0]->category}}/{{$method_group[0]->plugin_name}}/index.html" class="smp-nav-link">
                                    {{$method_group[0]->plugin_title}}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
                @endif

                {{-- メソッドのリストはプラグイン、メソッドで表示 --}}
                @if ($base_path == '../../' || $base_path == '../../../')
                <ul class="d-md-none">
                    @foreach ($methods->where('plugin_name', $current_method->plugin_name) as $method)
                        @if ($method->id == $current_method->id && $base_path == '../../../')
                            <li class="smp-nav-nolink">
                                {{$method->method_title}}
                            </li>
                        @else
                            <li class="smp-nav-link">
                                <a href="{{$base_path}}{{$method->category}}/{{$method->plugin_name}}/{{$method->method_name}}/index.html" class="smp-nav-link">
                                    {{$method->method_title}}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
                @endif
            @endif
            <div class="d-md-none">
                @include('manual.common.badge_menu')
            </div>
        </div>
    </nav>

    @yield('section_main')

    <footer class="container">
        <div class="card border-0 mt-2">
            <div class="card-body">
                <div class="text-center">
                    <a href="https://connect-cms.jp" target="_blank">Powered by Connect-CMS</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>