{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light py-1">
        <span class="d-md-none">処理選択</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
            @if ($function == "index")
                <li class="nav-item"><span class="nav-link"><span class="active">ページ一覧</span></span></li>
            @else
                <li class="nav-item"><a href="{{url('/manage/page')}}" class="nav-link">ページ一覧</a></li>
            @endif

            @if ($function == "edit" && empty($page->id))
                <li class="nav-item"><span class="nav-link"><span class="active">ページ登録</span></span></li>
            @else
                <li class="nav-item"><a href="{{url('/manage/page/edit')}}" class="nav-link">ページ登録</a></li>
            @endif

            @if ($function == "import")
                <li class="nav-item"><span class="nav-link"><span class="active">CSVインポート</span></span></li>
            @else
                <li class="nav-item"><a href="{{url('/manage/page/import')}}" class="nav-link">CSVインポート</a></li>
            @endif

            @if (($function == "edit" || $function == "role" || $function == "migration_order") && $page->id)
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onmouseover="this.click();this.blur();">
                        ページ変更
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">

                        @if ($function == "edit")
                            <a href="{{url('/manage/page/edit')}}/{{$page->id}}" class="dropdown-item active bg-light">ページ変更</a>
                        @else
                            <a href="{{url('/manage/page/edit')}}/{{$page->id}}" class="dropdown-item">ページ変更</a>
                        @endif

                        @if ($function == "role")
                            <a href="{{url('/manage/page/role')}}/{{$page->id}}" class="dropdown-item active bg-light">ページ権限設定</a>
                        @else
                            <a href="{{url('/manage/page/role')}}/{{$page->id}}" class="dropdown-item">ページ権限設定</a>
                        @endif

                        @if ($function == "migration_order")
                            <a href="{{url('/manage/page/migration_order')}}/{{$page->id}}" class="dropdown-item active bg-light">外部ページインポート</a>
                        @else
                            <a href="{{url('/manage/page/migration_order')}}/{{$page->id}}" class="dropdown-item">外部ページインポート</a>
                        @endif
                    </div>
                </li>
            @endif

            </ul>
        </div>
    </nav>
</div>
