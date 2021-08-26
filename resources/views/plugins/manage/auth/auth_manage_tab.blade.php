{{--
 * 管理画面tabテンプレート
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - 外部認証</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">認証設定</span></span>
                @else
                    <a href="{{url('/')}}/manage/auth" class="nav-link">認証設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                    @if ($function == "ldap")
                        <span class="nav-link"><span class="active">LDAP認証</span></span>
                    @else
                        <a href="{{url('/')}}/manage/auth/ldap" class="nav-link">LDAP認証</a></li>
                    @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "netcommons2")
                    <span class="nav-link"><span class="active">NetCommons2認証</span></span>
                @else
                    <a href="{{url('/')}}/manage/auth/netcommons2" class="nav-link">NetCommons2認証</a></li>
                @endif
                </li>

            </ul>
        </div>
    </nav>
</div>
