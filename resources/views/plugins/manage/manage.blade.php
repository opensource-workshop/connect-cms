{{--
 * 管理画面のメインテンプレート
 *
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
--}}
{{-- ベース画面 --}}
@extends('layouts.app')

{{-- 管理画面メイン部分への挿入 --}}
@section('content')

{{-- フラッシュメッセージ ヘッダー表示 --}}
@include('common.flash_message_for_header')

<div class="container-fluid">
    <div class="row mt-3">
        {{-- 管理メニュー --}}
        <aside class="col-lg-2 order-1">
            @include('plugins.manage.menus')
        </aside>

        <main class="col-lg-10 order-2" role="main">
            {{-- 管理画面各プラグインの画面内容 --}}
            @yield('manage_content')
        </main>
    </div>{{-- /row --}}
</div>{{-- /container-fluid --}}

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
