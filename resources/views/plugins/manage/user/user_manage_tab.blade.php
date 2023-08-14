{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択 - ユーザ管理</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                @if ($function == "index")
                    <span class="nav-link"><span class="active">ユーザ一覧</span></span>
                @else
                    <a href="{{url('/manage/user')}}" class="nav-link">ユーザ一覧</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "regist")
                    <span class="nav-link"><span class="active">ユーザ登録</span></span>
                @else
                    <a href="{{url('/manage/user/regist')}}" class="nav-link">ユーザ登録</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "originalRole")
                    <span class="nav-link"><span class="active">役割設定</span></span>
                @else
                    <a href="{{url('/manage/user/originalRole')}}" class="nav-link">役割設定</a></li>
                @endif
                </li>

                @if (config('connect.USE_USERS_COLUMNS_SET'))
                    <li role="presentation" class="nav-item">
                        @if ($function == "columnSets")
                            <span class="nav-link"><span class="active">項目セット一覧</span></span>
                        @else
                            <a href="{{url('/')}}/manage/user/columnSets" class="nav-link">項目セット一覧</a>
                        @endif
                    </li>

                    <li role="presentation" class="nav-item">
                        @if ($function == "registColumnSet")
                            <span class="nav-link"><span class="active">項目セット登録</span></span>
                        @else
                            <a href="{{url('/')}}/manage/user/registColumnSet" class="nav-link">項目セット登録</a>
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
                @else
                    <li role="presentation" class="nav-item">
                        @if ($function == "editColumns")
                            <span class="nav-link"><span class="active">項目設定</span></span>
                        @else
                            <a href="{{url('/manage/user/editColumns/1')}}" class="nav-link">項目設定</a></li>
                        @endif
                    </li>
                @endif

                @if ($function == 'editColumnDetail')
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">項目詳細設定</span></span>
                    </li>
                @endif

                <li role="presentation" class="nav-item">
                @if ($function == "autoRegist")
                    <span class="nav-link"><span class="active">自動ユーザ登録設定</span></span>
                @else
                    <a href="{{url('/manage/user/autoRegist/1')}}" class="nav-link">自動ユーザ登録設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                    @if ($function == "import")
                        <span class="nav-link"><span class="active">CSVインポート</span></span>
                    @else
                        <a href="{{url('/manage/user/import')}}" class="nav-link">CSVインポート</a></li>
                    @endif
                </li>

                <li role="presentation" class="nav-item">
                    @if ($function == "bulkDelete")
                        <span class="nav-link"><span class="active">一括削除</span></span>
                    @else
                        <a href="{{url('/manage/user/bulkDelete')}}" class="nav-link">一括削除</a></li>
                    @endif
                </li>

            </ul>
        </div>
    </nav>
</div>
