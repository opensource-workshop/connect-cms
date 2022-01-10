{{--
    管理者メニュー
--}}

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary rounded d-lg-none mb-3">
    <a class="navbar-brand" href="#"><span class="h6" id="manage_menu">管理者メニュー</span></a>
    <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#Navber" aria-controls="Navber" aria-expanded="false" aria-labelledby="manage_menu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse mt-2" id="Navber">
        @include('plugins.manage.menus_list')
        {{-- オプションプラグインの管理者メニュー --}}
        @includeIf('plugins_option.manage.menus_list')
    </div>
</nav>

<nav class="list-group d-none d-lg-block">
    @include('plugins.manage.menus_list')
    {{-- オプションプラグインの管理者メニュー --}}
    @includeIf('plugins_option.manage.menus_list')
</nav>
