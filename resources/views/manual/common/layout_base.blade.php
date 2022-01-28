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
        @include('manual.common.badge_menu')

        <!-- SmartPhone Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="メニュー">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto"></ul>
            <ul class="navbar-nav d-md-none">
                <li class="nav-item">
                    <a href="https://connect-cms.jp/" class="nav-link">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="https://connect-cms.jp/forum" class="nav-link">
                        フォーラム<i class="fas fa-plus"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="https://connect-cms.jp/about" class="nav-link">
                        Connect-CMSとは<i class="fas fa-plus"></i>
                    </a>
                </li>
            </ul>
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