{{--
    管理者メニューリスト
 --}}

<div class="list-group">
    @if (Auth::user()->can('role_manage_on'))
        @if (isset($plugin_name) && $plugin_name == 'index')
            <a href="{{url('/')}}/manage" class="list-group-item active">お知らせ</a>
        @else
            <a href="{{url('/')}}/manage" class="list-group-item">お知らせ</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_page'))
        @if (isset($plugin_name) && $plugin_name == 'page')
            <a href="{{url('/')}}/manage/page" class="list-group-item active">ページ管理</a>
        @else
            <a href="{{url('/')}}/manage/page" class="list-group-item">ページ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'site')
            <a href="{{url('/')}}/manage/site" class="list-group-item active">サイト管理</a>
        @else
            <a href="{{url('/')}}/manage/site" class="list-group-item">サイト管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_user'))
        @if (isset($plugin_name) && $plugin_name == 'user')
            <a href="{{url('/')}}/manage/user" class="list-group-item active">ユーザ管理</a>
        @else
            <a href="{{url('/')}}/manage/user" class="list-group-item">ユーザ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_user'))
        @if (isset($plugin_name) && $plugin_name == 'group')
            <a href="{{url('/')}}/manage/group" class="list-group-item active">グループ管理</a>
        @else
            <a href="{{url('/')}}/manage/group" class="list-group-item">グループ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'security')
            <a href="{{url('/')}}/manage/security" class="list-group-item active">セキュリティ管理</a>
        @else
            <a href="{{url('/')}}/manage/security" class="list-group-item">セキュリティ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'plugin')
            <a href="{{url('/')}}/manage/plugin" class="list-group-item active">プラグイン管理</a>
        @else
            <a href="{{url('/')}}/manage/plugin" class="list-group-item">プラグイン管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'system')
            <a href="{{url('/')}}/manage/system" class="list-group-item active">システム管理</a>
        @else
            <a href="{{url('/')}}/manage/system" class="list-group-item">システム管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'api')
            <a href="{{url('/')}}/manage/api" class="list-group-item active">API管理</a>
        @else
            <a href="{{url('/')}}/manage/api" class="list-group-item">API管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'message')
            <a href="{{url('/')}}/manage/message" class="list-group-item active">メッセージ管理</a>
        @else
            <a href="{{url('/')}}/manage/message" class="list-group-item">メッセージ管理</a>
        @endif
    @endif
    <div class="list-group-item text-secondary bg-light">データ管理系</div>
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'uploadfile')
            <a href="{{url('/')}}/manage/uploadfile" class="list-group-item active">アップロードファイル管理</a>
        @else
            <a href="{{url('/')}}/manage/uploadfile" class="list-group-item">アップロードファイル管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'theme')
            <a href="{{url('/')}}/manage/theme" class="list-group-item active">テーマ管理</a>
        @else
            <a href="{{url('/')}}/manage/theme" class="list-group-item">テーマ管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'number')
            <a href="{{url('/')}}/manage/number" class="list-group-item active">連番管理</a>
        @else
            <a href="{{url('/')}}/manage/number" class="list-group-item">連番管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_site'))
        @if (isset($plugin_name) && $plugin_name == 'code')
            <a href="{{url('/')}}/manage/code" class="list-group-item active">コード管理</a>
        @else
            <a href="{{url('/')}}/manage/code" class="list-group-item">コード管理</a>
        @endif
    @endif
    @if (Auth::user()->can('admin_system'))
        @if (isset($plugin_name) && $plugin_name == 'log')
            <a href="{{url('/')}}/manage/log" class="list-group-item active">ログ管理</a>
        @else
            <a href="{{url('/')}}/manage/log" class="list-group-item">ログ管理</a>
        @endif
    @endif
</div>
