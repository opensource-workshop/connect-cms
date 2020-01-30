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
                <li class="nav-item"><span class="nav-link"><span class="active">ページ追加・一覧</span></span></li>
            @else
                <li class="nav-item"><a href="{{url('/manage/page')}}" class="nav-link">ページ追加・一覧</a></li>
            @endif
            @if ($function == "import")
                <li class="nav-item"><span class="nav-link"><span class="active">CSVインポート</span></span></li>
            @else
                <li class="nav-item"><a href="{{url('/manage/page/import')}}" class="nav-link">CSVインポート</a></li>
            @endif
            </ul>
        </div>
    </nav>
</div>
