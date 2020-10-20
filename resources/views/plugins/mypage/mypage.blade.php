{{--
    マイページ画面のメインテンプレート
--}}
{{-- ベース画面 --}}
@extends('layouts.app')

{{-- マイページ画面メイン部分への挿入 --}}
@section('content')

<div class="container">
    <div class="row mt-3">

        {{-- マイページメニュー --}}
        <aside class="col-lg-3 order-1">
            @include('plugins.mypage.menus')
        </aside>

        <main class="col-lg-9 order-2" role="main">

            {{-- マイページ画面各プラグインの画面内容 --}}
            @yield('mypage_content')

        </main>

    </div>{{-- /row --}}
</div>{{-- /container --}}

<footer class="container">
    <div class="card border-0 mt-2">
        <div class="card-body">
            <div class="text-center">
                <a href="https://connect-cms.jp" target="_blank">Powered by Connect-CMS</a>
            </div>
        </div>
    </div>
</footer>
@endsection
