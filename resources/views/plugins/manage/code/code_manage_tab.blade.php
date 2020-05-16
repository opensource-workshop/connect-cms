{{--
 * 管理画面tabテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
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
                    <a href="{{url('/')}}/manage/code" class="nav-link">コード一覧</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "regist")
                    <span class="nav-link"><span class="active">コード登録</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/regist" class="nav-link">コード登録</a></li>
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
                    <a href="{{url('/')}}/manage/code/display" class="nav-link">表示設定</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "searches")
                    <span class="nav-link"><span class="active">検索条件一覧</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/searches" class="nav-link">検索条件一覧</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "searchRegist")
                    <span class="nav-link"><span class="active">検索条件登録</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/searchRegist" class="nav-link">検索条件登録</a></li>
                @endif
                </li>

                @if ($function == "searchEdit")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">検索条件変更</span></span>
                    </li>
                @endif

                <li role="presentation" class="nav-item">
                @if ($function == "helpMessages")
                    <span class="nav-link"><span class="active">注釈一覧</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/helpMessages" class="nav-link">注釈一覧</a></li>
                @endif
                </li>

                <li role="presentation" class="nav-item">
                @if ($function == "helpMessageRegist")
                    <span class="nav-link"><span class="active">注釈登録</span></span>
                @else
                    <a href="{{url('/')}}/manage/code/helpMessageRegist" class="nav-link">注釈登録</a></li>
                @endif
                </li>

                @if ($function == "helpMessageEdit")
                    <li role="presentation" class="nav-item">
                        <span class="nav-link"><span class="active">注釈変更</span></span>
                    </li>
                @endif

            </ul>
        </div>
    </nav>
</div>
