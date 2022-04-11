{{--
 * 管理画面tabテンプレート
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - 施設管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                    @if ($function == "index")
                        <span class="nav-link"><span class="active">施設一覧</span></span>
                    @else
                        <a href="{{url('/')}}/manage/reservation" class="nav-link">施設一覧</a>
                    @endif
                </li>

                <li role="presentation" class="nav-item">
                    @if ($function == "regist")
                        <span class="nav-link"><span class="active">施設登録</span></span>
                    @else
                        <a href="{{url('/')}}/manage/reservation/regist" class="nav-link">施設登録</a>
                    @endif
                </li>

                @if ($function == "edit")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">施設変更</span></span>
                    </li>
                @endif

                <li role="presentation" class="nav-item">
                    @if ($function == "categories")
                        <span class="nav-link"><span class="active">施設カテゴリ設定</span></span>
                    @else
                        <a href="{{url('/')}}/manage/reservation/categories" class="nav-link">施設カテゴリ設定</a>
                    @endif
                </li>

                <li role="presentation" class="nav-item">
                    @if ($function == "columnSets")
                        <span class="nav-link"><span class="active">項目セット一覧</span></span>
                    @else
                        <a href="{{url('/')}}/manage/reservation/columnSets" class="nav-link">項目セット一覧</a>
                    @endif
                </li>

                <li role="presentation" class="nav-item">
                    @if ($function == "registColumnSet")
                        <span class="nav-link"><span class="active">項目セット登録</span></span>
                    @else
                        <a href="{{url('/')}}/manage/reservation/registColumnSet" class="nav-link">項目セット登録</a>
                    @endif
                </li>

                @if ($function == "editColumnSet")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">項目セット変更</span></span>
                    </li>
                @endif

                @if ($function == "editColumns")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">項目設定</span></span>
                    </li>
                @endif

                @if ($function == 'editColumnDetail')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">項目詳細設定</span></span>
                    </li>
                @endif

                <li role="presentation" class="nav-item">
                    @if ($function == "bookings")
                        <span class="nav-link"><span class="active">予約一覧</span></span>
                    @else
                        <a href="{{url('/')}}/manage/reservation/bookings" class="nav-link">予約一覧</a>
                    @endif
                </li>
            </ul>
        </div>
    </nav>
</div>
