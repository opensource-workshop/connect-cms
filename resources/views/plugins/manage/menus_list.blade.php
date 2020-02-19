{{--
    管理者メニューリスト
 --}}

<div class="list-group">
    @if (Auth::user()->can('role_manage_on'))
        @if (isset($plugin_name) && $plugin_name == 'index')
            <a href="/manage" class="list-group-item active">お知らせ</a>
        @else
            <a href="/manage" class="list-group-item">お知らせ</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_page'))
        @if (isset($plugin_name) && $plugin_name == 'page')
            <a href="/manage/page" class="list-group-item active">ページ管理</a>
        @else
            <a href="/manage/page" class="list-group-item">ページ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'site')
            <a href="/manage/site" class="list-group-item active">サイト管理</a>
        @else
            <a href="/manage/site" class="list-group-item">サイト管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_user'))
        @if (isset($plugin_name) && $plugin_name == 'user')
            <a href="/manage/user" class="list-group-item active">ユーザ管理</a>
        @else
            <a href="/manage/user" class="list-group-item">ユーザ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'security')
            <a href="/manage/security" class="list-group-item active">セキュリティ管理</a>
        @else
            <a href="/manage/security" class="list-group-item">セキュリティ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'plugin')
            <a href="/manage/plugin" class="list-group-item active">プラグイン管理</a>
        @else
            <a href="/manage/plugin" class="list-group-item">プラグイン管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'system')
            <a href="/manage/system" class="list-group-item active">システム管理</a>
        @else
            <a href="/manage/system" class="list-group-item">システム管理</a>
        @endif
    @endif
    <div class="list-group-item text-secondary bg-light">データ管理系</div>
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'theme')
            <a href="/manage/theme" class="list-group-item active">テーマ管理</a>
        @else
            <a href="/manage/theme" class="list-group-item">テーマ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'number')
            <a href="/manage/number" class="list-group-item active">連番管理</a>
        @else
            <a href="/manage/number" class="list-group-item">連番管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'code')
            <a href="/manage/code" class="list-group-item active">コード管理</a>
        @else
            <a href="/manage/code" class="list-group-item">コード管理</a>
        @endif
    @endif
</div>
