{{--
    マイページメニュー
--}}

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary rounded d-lg-none mb-3">
    <a class="navbar-brand" href="#"><span class="h6">マイページメニュー</span></a>
    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#Navber" aria-controls="Navber" aria-expanded="false" aria-label="ナビゲーションの切替">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse mt-2" id="Navber">
        @include('plugins.mypage.menus_list')
    </div>
</nav>

<div class="list-group d-none d-lg-block">
    @include('plugins.mypage.menus_list')
</div>
