{{--
    管理者メニュー
 --}}
<div class="list-group">
{{--
    <a href="/manage/pluginadd/" class="list-group-item">プラグイン追加</a>
--}}
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
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'plugin')
            <a href="/manage/plugin" class="list-group-item active">プラグイン管理</a>
        @else
            <a href="/manage/plugin" class="list-group-item">プラグイン管理</a>
        @endif
    @endif
</div>
