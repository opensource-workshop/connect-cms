{{--
 * 管理画面tabテンプレート
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - コード管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">コード一覧</span></span>
                @else
                    <a href="{{url('/')}}/manage/code" class="nav-link">コード一覧</a>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "regist")
                    <span class="nav-link"><span class="active">コード登録</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/regist" class="nav-link">コード登録</a>
                @endif
                </li>

                @if ($function == "edit")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">コード変更</span></span>
                    </li>
                @endif

                <li role="presentation" class="nav-item">
                @if ($function == "display")
                    <span class="nav-link"><span class="active">表示設定</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/display" class="nav-link">表示設定</a>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "searches")
                    <span class="nav-link"><span class="active">検索条件一覧</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/searches" class="nav-link">検索条件一覧</a>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "searchRegist")
                    <span class="nav-link"><span class="active">検索条件登録</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/searchRegist" class="nav-link">検索条件登録</a>
                @endif
                </li>

                @if ($function == "searchEdit")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">検索条件変更</span></span>
                    </li>
                @endif

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();">
                        その他設定
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">

                        @if ($function == "helpMessages")
                            <a href="{{url('/')}}/manage/code/helpMessages" class="dropdown-item active bg-light">注釈一覧</a>
                        @else
                            <a href="{{url('/')}}/manage/code/helpMessages" class="dropdown-item">注釈一覧</a>
                        @endif

                        @if ($function == "helpMessageRegist")
                            <a href="{{url('/')}}/manage/code/helpMessageRegist" class="dropdown-item active bg-light">注釈登録</a>
                        @else
                            <a href="{{url('/')}}/manage/code/helpMessageRegist" class="dropdown-item">注釈登録</a>
                        @endif

                        @if ($function == "helpMessageEdit")
                            <span class="dropdown-item active bg-light">注釈変更</span>
                        @endif

                        @if ($function == "import")
                            <a href="{{url('/')}}/manage/code/import" class="dropdown-item active bg-light">インポート</a>
                        @else
                            <a href="{{url('/')}}/manage/code/import" class="dropdown-item">インポート</a>
                        @endif

                        @if ($function == "download")
                            <a href="{{url('/')}}/manage/code/download" class="dropdown-item active bg-light">ダウンロード</a>
                        @else
                            <a href="{{url('/')}}/manage/code/download" class="dropdown-item">ダウンロード</a>
                        @endif
                    </div>
                </li>

            </ul>
        </div>
    </nav>
</div>
