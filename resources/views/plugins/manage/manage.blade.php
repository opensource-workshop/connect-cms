{{--
    管理画面のメインテンプレート
 --}}
{{-- ベース画面 --}}
@extends('layouts.app')

{{-- 管理画面メイン部分への挿入 --}}
@section('content')

<div class="container">
    <div class="row mt-3">

        {{-- 管理メニュー --}}
        <div class="col-lg-3 order-1">
            @include('plugins.manage.menus')
        </div>

        <div class="col-lg-9 order-2">

<?php
//    PHPでクラスを呼ぶ際のサンプル
//    $class_name = "App\ManagePlugins\PageManager\PageManager";


//    $class_name = "App\ManagePlugins\\" . $manage_class . "\\" . $manage_class;
//    $PageManager = new $class_name;
//    $method = "init";
//    echo $PageManager->$method(app('request'));

?>

            {{-- 管理画面各プラグインの画面内容 --}}
            @yield('manage_content')

        </div>

    </div>{{-- /row --}}
</div>{{-- /container --}}

<div class="container">
    <div class="card border-0 mt-2">
        <div class="card-body">
            <div class="text-center">
                <a href="https://connect-cms.jp" target="_blank">Powered by Connect-CMS</a>
            </div>
        </div>
    </div>
</div>
@endsection
